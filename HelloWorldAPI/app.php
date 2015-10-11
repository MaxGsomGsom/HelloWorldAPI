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
            $userDialog1->dialog_id = $dialog->dialog_id;
            $success2 = $userDialog1->save();

            $userDialog2 = new UserDialog();
            $userDialog2->login = $opponent->login;
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


        $dialog = Dialog::findFirst(
            array(
                "dialog_id = :dialog_id:",
                'bind' => array(
                    'dialog_id'    => $req->get("dialog_id")
                )
            )
        );

        if ($dialog) {


            $myLogin = $app->session->get("auth")["login"];

            $message = new Message();
            $message->login = $myLogin;
			$message->dialog_id = $req->get("dialog_id");
			$message->time = time();
			$message->text = $req->get("text");
            $success1 = $message->save();
			

            if ($success1) {

                $dialog->time = $message->time;
                $success2 = $dialog->save();

                if ($success2) {
                    return Status(true);
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
                "users" => $usersData
                );


        }

		$res = new Response();
        $res->setJsonContent($data);
		return $res;

    }

    return Status(false);

});


//dialog_id, time
$app->get('/message/show', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;

        $messages = Message::find(
            array(
                "dialog_id = :dialog_id: AND time>:time:",
                'bind' => array(
                    'dialog_id'    => $req->get("dialog_id"),
                    'time' => $req->get("time")
                )
            )
        );

        $data = array();


        foreach ($messages as $message) {

            $data[] = array(
                "login" => $message->login,
                "time" => $message->time,
                "text" => $message->text);
        }

        $res = new Response();
        $res->setJsonContent($data);
        return $res;

    }

    return Status(false);

});



//query
$app->get('/user/search', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;

        $users = User::find(
            array(
                "login LIKE :login: OR name LIKE :name: OR info LIKE :info:",
                'bind' => array(
                    'login'    => "%".$req->get("query")."%",
                    'name' => "%".$req->get("query")."%",
                    'info' => "%".$req->get("query")."%"
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



