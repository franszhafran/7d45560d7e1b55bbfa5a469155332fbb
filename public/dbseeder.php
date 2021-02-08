<?php
require_once 'functions.php';

use Josantonius\Request\Request;

if(Request::isGet()) {
    DB::insertUser("test", md5("test123"), "http://localhost:8082/receiver.php");    
}
