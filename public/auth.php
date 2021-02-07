<?php
require_once 'functions.php';

use Josantonius\Request\Request;

if(Request::isPost()) {
    auth(getRequest());
}

function auth($request) {
    $user = DB::authUser($request->username, $request->password);

    if(!$user) {
        returnUnauthorized();
    }

    returnSuccess([
        "token" => $user
    ]);
}
