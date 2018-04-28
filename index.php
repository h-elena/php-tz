#!/usr/bin/php
<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include_once __DIR__ . '/lib/Users.php';

$obj = new \lib\Users();
if ($obj->addUsers()) {
    echo 'Все пользователи добавлены в базу.';
}

