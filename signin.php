<?php
require_once 'database.php';

$loginErrors = [];

if (isset($_POST["login"])) {

    $email    = trim($_POST["login-email"] ?? "");
    $password = $_POST["login-password"] ?? "";

    if ($email === "" || $password === "") {
        $loginErrors[] = "Merci de saisir votre email et votre mot de passe";
    } else {
        try {
            $bdd = connect_bdd();
            $stmt = $bdd->prepare(
                "SELECT id_user, firstname, password, id_role FROM users WHERE email = ?"
            );
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user["password"])) {
                $loginErrors[] = "Email ou mot de passe incorrect";
            } else {
                session_regenerate_id(true);

                $_SESSION["user"] = [
                    "id"        => $user["id_user"],
                    "firstname" => $user["firstname"],
                    "id_role"   => $user["id_role"],
                ];

                header("Location: index.php");
                exit;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $loginErrors[] = "Une erreur est survenue, réessaie plus tard";
        }
    }
}