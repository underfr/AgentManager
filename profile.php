<?php

session_start();
require_once "database.php";
require_once "auth.php";

require_login();
$profileError = "";
$user = null;
try {
    $bdd = connect_bdd();
    $stmt = $bdd->prepare("SELECT u.firstname,u.lastname,u.email,u.created_at, r.role FROM users u INNER JOIN roles r ON u.id_role = r.id_role WHERE u.id_user = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch();
    if (!$user){
        header("Location: index.php");
        exit;
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $profileError = "Profile indisponible";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <title>Profil</title>
</head>
<body>
<main>
    <h1>Mon profil</h1>

    <?php if ($profileError !== ""): ?>
        <p class="alert alert-error"><?= htmlspecialchars($profileError) ?></p>
    <?php else: ?>
        <p class="info"><strong>Prénom :</strong> <?= htmlspecialchars($user["firstname"]) ?></p>
        <p class="info"><strong>Nom :</strong> <?= htmlspecialchars($user["lastname"]) ?></p>
        <p class="info"><strong>Email :</strong> <?= htmlspecialchars($user["email"]) ?></p>
        <p class="info"><strong>Rôle :</strong> <?= htmlspecialchars($user["role"]) ?></p>
        <p class="info"><strong>Inscrit le :</strong> <?= $user["created_at"] ? date("d/m/Y", strtotime($user["created_at"])) : "—" ?></p>
    <?php endif; ?>

    <p><a href="index.php" class="back">Retour à l'accueil</a></p>

    <form method="post" action="signout.php" class="inline">
        <input type="submit" name="signout" value="Sign Out" class="btn-danger">
    </form>
</main>
</body>
</html>