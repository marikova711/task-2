<?php
$dbhost = "localhost";
$dblogin = "root";
$dbpass = "";
$dbname = "equipment";
$tablename = "transformers";


    $mysql = new mysqli($dbhost, $dblogin, $dbpass, $dbname);
    $mysql->query("SET NAMES 'utf8'");

    if ($mysql->connect_error) {
        die("Ошибка подключения к базе данных: " . $mysql->connect_error);
    } else {
        $mysql->query("TRUNCATE TABLE `$tablename`");
    }

    header('Location: http://task2');
    exit;
