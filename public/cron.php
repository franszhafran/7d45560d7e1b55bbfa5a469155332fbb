<?php
require_once 'functions.php';

use Josantonius\Request\Request;

if(Request::isGet()) {
    runCron();    
}

function runCron() {
    $take = 1;

    $queue = getTopQueue($take);

    try {
        DB::moveQueueToSent($queue);

        returnSuccess($queue);
    } catch (Exception $e) {
        return returnBadRequest($e->getMessage());
    }
}