<?php
require_once('database.php');

$managers = [];
$managersErrors = "";
$role = 2;

if (isset($_SESSION['user'])) {
    $id_user = $_SESSION['user']["id"];
    try {
        $bdd = connect_bdd();
        $stmt = $bdd->prepare("SELECT firstname,lastname,email,created_at FROM `users` WHERE id_user != ? AND id_role = ?");
        $stmt->execute([$id_user, $role]);
        $managers = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $managersErrors = "Impossible de charger la liste des managers." ;
    }
}

