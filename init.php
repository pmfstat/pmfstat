<?php

include('./auth.php');
include('./helper.php');
include('./iteratoren.php');


######################################

if ($_ENV['HOSTNAME'] == 'johannes.nop') {
    $db_server = 'localhost'; $db_name = 'pmf_stat'; $db_user = 'root'; $db_pw = '';
} else {
    require_once('../inc/sql.php');
}

try {
    $pdo = new PDODebug('mysql:host='.$db_server.';dbname='.$db_name, $db_user, $db_pw);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Internal server error while processing statistic data: Couldn't connect to database");
}

include('./data.php');
