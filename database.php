<?php

function connect_bdd(): PDO
{
    $dsn = 'mysql:dbname=agent_manager;host=127.0.0.1';
    $user = 'root';
    $password = 'root';

    return new PDO(
        $dsn,
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}