<?php

use Phalcon\Http\Response;

/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

/**
 * Add your routes here
 */
//$app->get('/', function () use ($app) {
//    echo $app['view']->render('index');
//});

//login, pass, name, info
$app->get('/register', function () use ($app) {

    $auth=$app->session->get("auth");

    if (!$auth) {


        $req = $app->request;

        $userInDB = User::findFirst(
            array(
                "login = :login:",
                'bind' => array(
                    'login'    => $req->get("login")
                )
            )
        );

        if (!$userInDB) {

            $user = new User();
            $user->login = $req->get("login");
            $user->pass = md5($req->get("pass"));
            $user->name = $req->get("name");
            $user->info = $req->get("info");

            $success = $user->save();


            if ($success) {
                return Status(true);
            }
        }

    }

    return Status(false);

});


//login, pass
$app->get('/login', function () use ($app) {

    $auth=$app->session->get("auth");

    if (!$auth) {

        $req = $app->request;

        $user = User::findFirst(
            array(
                "login = :login: AND pass = :pass:",
                'bind' => array(
                    'login'    => $req->get("login"),
                    'pass' => md5($req->get("pass"))
                )
            )
        );


        if ($user) {

            $app->session->set(
                'auth',
                array(
                    'login' => $user->login
                )
            );

            return Status(true);
        }


    }

    return Status(false);

});


//login
$app->get('/dialog/add', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;


        $opponent = User::findFirst(
            array(
                "login = :user:",
                'bind' => array(
                    'user'    => $req->get("login")
                )
            )
        );

        if ($opponent) {


            $myLogin = $app->session->get("auth")["login"];

            $dialog = new Dialog();
            $dialog->name = ($myLogin).'-'.($opponent->login);
            $dialog->time = time();
            $success1 = $dialog->save();


            $userDialog1 = new UserDialog();
            $userDialog1->login = $myLogin;
            $userDialog1->new = true;
            $userDialog1->dialog_id = $dialog->dialog_id;
            $success2 = $userDialog1->save();

            $userDialog2 = new UserDialog();
            $userDialog2->login = $opponent->login;
            $userDialog2->new = true;
            $userDialog2->dialog_id = $dialog->dialog_id;
            $success3 = $userDialog2->save();

            if ($success1 && $success2 && $success3) {
                return Status(true);
            }


        }

    }

    return Status(false);

});


//dialog_id, text
$app->get('/message/add', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;
        $myLogin = $app->session->get("auth")["login"];

        $userDialog = UserDialog::findFirst(
            array(
                "dialog_id = :dialog_id: AND login = :login:",
                'bind' => array(
                    'dialog_id'    => $req->get("dialog_id"),
                    'login' => $myLogin
                )
            )
        );

        if ($userDialog) {


            $dialog=$userDialog->dialog;

            if ($dialog) {

                $message = new Message();
                $message->login = $myLogin;
                $message->dialog_id = $req->get("dialog_id");
                $message->time = time();


                $str = $req->get("text");

                $message->text = $str;

                $success1 = $message->save();


                if ($success1) {

                    $dialog->time = $message->time;
                    $success2 = $dialog->save();

                    $userDialogsAll = $dialog->UserDialog;

                    foreach ($userDialogsAll as $item) {
                        if ($item->login != $myLogin) {
                            $item->new = true;
                            $item->save();
                        }
                    }


                    if ($success2) {
                        return Status(true);
                    }
                }
            }

        }

    }

    return Status(false);

});


$app->get('/dialog/show', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

		$myLogin = $app->session->get("auth")["login"];
		
        $userDialogs = UserDialog::find(
            array(
                "login = :login:",
                'bind' => array(
                    'login'    => $myLogin
                )
            )
        );
		
		$data = array();


		foreach ($userDialogs as $userDialog) {

			$dialog = $userDialog->dialog;

            $users = UserDialog::find(
                array(
                    "dialog_id = :dialog_id: AND NOT login = :login:",
                    'bind' => array(
                        'dialog_id'    => $dialog->dialog_id,
                        'login'    => $myLogin
                    )
                )
            );

            $usersData = "";

            foreach ($users as $user) {
                $usersData = $usersData. $user->login.", ";
            }

            $usersData = rtrim($usersData, ", ");

            $data[] = array(
                "dialog_id" => $dialog->dialog_id,
                "name" => $dialog->name,
                "time" => $dialog->time,
                "users" => $usersData,
                "new"  => $userDialog->new
                );


        }

		$res = new Response();
        $res->setJsonContent($data);
		return $res;

    }

    return Status(false);

});


$app->get('/dialog/check', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $myLogin = $app->session->get("auth")["login"];

        $userDialogs = UserDialog::find(
            array(
                "login = :login:",
                'bind' => array(
                    'login'    => $myLogin
                )
            )
        );

        $data = array();


        foreach ($userDialogs as $userDialog) {

            $dialog = $userDialog->dialog;

            if ($userDialog->new == true) {
                $data[] = array(
                    "dialog_id" => $dialog->dialog_id,
                    "name" => $dialog->name,
                    "time" => $dialog->time,
                    "new"  => $userDialog->new
                );
            }


        }

        if (count($data)>0) {
            $res = new Response();
            $res->setJsonContent($data);
            return $res;
        }
        else {
            return Status(true);
        }

    }

    return Status(false);

});


//dialog_id, time
$app->get('/message/show', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;
        $myLogin = $app->session->get("auth")["login"];

        $userDialog = UserDialog::findFirst(array(
            "dialog_id = :dialog_id: AND login = :login:",
            'bind' => array(
                'dialog_id'    => $req->get("dialog_id"),
                'login' => $myLogin
            )
        ));



        if ($userDialog ) {
            $dialog = $userDialog->dialog;

            if ($dialog) {

                $messages = $dialog->message;


                $data = array();

                foreach ($messages as $message) {

                    if ($req->get("time") < $message->time) {
                        $data[] = array(
                            "login" => $message->login,
                            "time" => $message->time,
                            "text" => $message->text,
                            "message_id" => $message->message_id);
                    }
                }

                $res = new Response();
                $res->setJsonContent($data);


                $userDialog->new = false;
                $userDialog->save();


                return $res;
            }
        }

    }

    return Status(false);

});


//query
$app->get('/user/search', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;
        $myLogin = $app->session->get("auth")["login"];

        $users = User::find(
            array(
                "(login LIKE :login: OR name LIKE :name: OR info LIKE :info:) AND login != :myLogin:",
                'bind' => array(
                    'login'    => "%".$req->get("query")."%",
                    'name' => "%".$req->get("query")."%",
                    'info' => "%".$req->get("query")."%",
                    'myLogin' => $myLogin
                )
            )
        );

        $data = array();


        foreach ($users as $user) {

            $data[] = array(
                'login' => $user->login,
                'name' => $user->name,
                'info' => $user->info
            );
        }

        $res = new Response();
        $res->setJsonContent($data);
        return $res;

    }

    return Status(false);

});


//login
$app->get('/user/show', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;

        $user = User::findFirst(
            array(
                "login=:login:",
                'bind' => array(
                    'login' => $req->get("login")
                )
            )
        );

        if ($user) {

            $data = array(
                'login' => $user->login,
                'name' => $user->name,
                'info' => $user->info
            );

            $res = new Response();
            $res->setJsonContent($data);
            return $res;

        }
    }

    return Status(false);

});


//dialog_id
$app->get('/dialog/delete', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;
        $myLogin = $app->session->get("auth")["login"];

        $userDialog = UserDialog::findFirst(
            array(
                "dialog_id = :dialog_id: AND login = :login:",
                'bind' => array(
                    'dialog_id'    => $req->get("dialog_id"),
                    'login' => $myLogin
                )
            )
        );

        if ($userDialog) {

            $userDialog->delete();
            return Status(true);
        }

    }

    return Status(false);

});


//dialog_id, name
$app->get('/dialog/rename', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;
        $myLogin = $app->session->get("auth")["login"];

        $userDialog = UserDialog::findFirst(
            array(
                "dialog_id = :dialog_id: AND login = :login:",
                'bind' => array(
                    'dialog_id'    => $req->get("dialog_id"),
                    'login' => $myLogin
                )
            )
        );

        if ($userDialog) {

            $dialog = $userDialog->dialog;

            $dialog->name = $req->get("name");
            $succsess = $dialog->save();

            if ($succsess) {
                return Status(true);
            }
        }

    }

    return Status(false);

});


//
$app->get('/login/check', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $myLogin = $app->session->get("auth")["login"];

        $res = new Response();
        $res->setJsonContent(array("login"=>$myLogin));
        return $res;

    }

    return Status(false);

});


//oldpass, newpass, name, info
$app->get('/user/change', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;
        $myLogin = $app->session->get("auth")["login"];

        $user = User::findFirst(
            array(
                "login = :login:",
                'bind' => array(
                    'login' => $myLogin
                )
            )
        );

        $oldPass = md5($req->get("oldpass"));
        if ($user && $oldPass == $user->pass) {

            $user->pass = md5($req->get("newpass"));
            $user->name = $req->get("name");
            $user->info = $req->get("info");

            $succsess = $user->save();

            if ($succsess) {
                return Status(true);
            }
        }

    }

    return Status(false);

});


//
$app->post('/avatar/upload', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $myLogin = $app->session->get("auth")["login"];
        //$reqFile = $app->request->getUploadedFiles()[0];
        //$fileData = file_get_contents($reqFile->getTempName());

        $user = User::findFirst(
            array(
                "login = :login:",
                'bind' => array(
                    'login' => $myLogin
                )
            )
        );

        if ($user /*&& $fileData*/) {

            $avaAll = $user->getImage();

            if (count($avaAll) == 0) {
                $ava = new Image();
                $ava->login = $myLogin;
            }
            else {
                $ava = $avaAll[0];
            }
            $ava->img = $app->request->getRawBody();//$fileData;

            $succsess = $ava->save();

            if ($succsess) {
                return Status(true);
            }
        }

    }

    return Status(false);

});


//login
$app->get('/avatar/show', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;

        $login = $req->get("login");
        if ($login == null) {
            $login = $app->session->get("auth")["login"];
        }

        $user = User::findFirst(
            array(
                "login = :login:",
                'bind' => array(
                    'login' => $login
                )
            )
        );

        if ($user) {

            $ava = $user->getImage()[0];

            if ($ava) {
                $res = new Response();
                $res->setContent($ava->img);

                $res->setHeader("Content-Type", "image/jpeg");

                return $res;
            }
        }

    }

    return Status(false);

});



/**
 * Not found handler
 */
$app->notFound(function () use ($app) {

    $app->response->setStatusCode(404, "Not Found")->sendHeaders();

    return Status(false);

});


function Status($val){

    $res = new Response();

    if ($val==true){
        $res->setJsonContent(array("status"=>"true"));
    }
    else {
        $res->setJsonContent(array("status"=>"false"));
    }

    return $res;
}



