<?php
require_once 'functions.php';

use Josantonius\Request\Request;

if(Request::isGet()) {
    DB::insertUser("frans", "test123", "http://google.co.id");    
}
