<?php

    $hostname   = "mysql-zonemsp";
    $root       = "root";
    $password   = "root123"; 
    $database   = "mess"; 
    $connection = new mysqli($hostname, $root, $password , $database);

    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    } 
?>