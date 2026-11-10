<?php

function require_login()
{
    if (!isset($_SESSION['user'])) {
        header('Location: index.php');
        exit();
    }
}

function require_manager()
{
    require_login();
    if ($_SESSION['user']['id_role'] !== 2) {
        header('Location: index.php');
        exit();
    }
}