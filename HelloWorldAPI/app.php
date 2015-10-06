<?php

use Phalcon\Http\Response;

/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

/**
 * Add your routes here
 */
$app->get('/', function () use ($app) {
    echo $app['view']->render('index');
});


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




$app->get('/dialog/add', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;


        $opponent = User::findFirst(
            array(
                "login = :user:",
                'bind' => array(
                    'user'    => $req->get("user")
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



$app->get('/dialog/delete', function () use ($app) {

    $auth=$app->session->get("auth");

    if ($auth) {

        $req = $app->request;
        $myLogin = $app->session->get("auth")["login"];

        $userDialog = UserDialog::findFirst(
            array(
                "dialog_id = :dialog_id: AND login = :login:",
                'bind' => array(
                    'dialog_id'    => $req->get("id"),
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


