<?php
require_once 'functions.php';

use Josantonius\Request\Request;

if(Request::isPost()) {
    sendEmailToQueue(getRequest());    
}

function sendEmailToQueue($request) {
    $user = getUserFromToken($request->token->token, $request->token->type);

    if(!$user) {
        returnUnauthorized();
    }

    try {
        returnSuccess(DB::sendEmailToQueue($request, $user['id']));
    } catch (Exception $e) {
        return returnBadRequest($e->getMessage());
    }
}