<?php

try {
    $dsn = "mysql: host=localhost;port=3307;dbname=database";
    $username = 'root';
    $password = 'root';
    $db = new PDO($dsn, $username,$password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Unable to connect:  ";
    echo $e->getMessage();
    exit;
}