<?php
require_once 'database.php';

$errors = [];
$success = "";
$id_role = 2;

function sanitize(string $data): string
{
    return trim($data);
}

function verify_firstname(string $data): ?string
{
    return mb_strlen($data) < 2 ? "Le prénom doit faire au moins 2 caractères" : null;
}

function verify_lastname(string $data): ?string
{
    return mb_strlen($data) < 2 ? "Le nom doit faire au moins 2 caractères" : null;
}

function verify_email(string $data): ?string
{
    return filter_var($data, FILTER_VALIDATE_EMAIL) ? null : "Le format du mail est invalide";
}

function verify_password(string $data): ?string
{
    if (mb_strlen($data) < 8) return "Le mot de passe doit faire au moins 8 caractères";
    if (!preg_match("/[A-Z]/", $data)) return "Le mot de passe doit contenir au moins une majuscule";
    if (!preg_match("/[a-z]/", $data)) return "Le mot de passe doit contenir au moins une minuscule";
    if (!preg_match("/[0-9]/", $data)) return "Le mot de passe doit contenir au moins un chiffre";
    return null;
}

function verify_password_confirmation(string $password, string $confirmPassword): ?string
{
    return $password !== $confirmPassword ? "Les mots de passe ne correspondent pas" : null;
}

if (isset($_POST["submit"])) {
    $firstname       = sanitize($_POST["firstname"] ?? "");
    $lastname        = sanitize($_POST["lastname"] ?? "");
    $email           = sanitize($_POST["email"] ?? "");
    $password        = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm-password"] ?? "";

    if ($firstname === "" || $lastname === "" || $email === "" || $password === "" || $confirmPassword === "") {
        $errors[] = "Tous les champs sont obligatoires";
    } else {
        $errors = array_filter([
            verify_firstname($firstname),
            verify_lastname($lastname),
            verify_email($email),
            verify_password($password),
            verify_password_confirmation($password, $confirmPassword),
        ]);
    }

    if (empty($errors)) {
        try {
            $bdd = connect_bdd();

            $stmt = $bdd->prepare("SELECT id_user FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = "Cet email est déjà utilisé";
            } else {
                $stmt = $bdd->prepare(
                    "INSERT INTO users (firstname, lastname, email, password, Id_Role) VALUES (?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $firstname,
                    $lastname,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT),
                    $id_role,
                ]);
                $success = "Inscription réussie, bienvenue $firstname $lastname";
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = "Une erreur est survenue, réessaie plus tard";
        }
    }
}