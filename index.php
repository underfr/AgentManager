<?php
session_start();

require_once 'database.php';
require_once 'register.php';
require_once 'signin.php';
require_once 'managers.php';

$isLogged = isset($_SESSION["user"]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <title>Accueil</title>
</head>
<body>
<main>

    <?php if ($isLogged): ?>

        <h1>Bonjour <?= htmlspecialchars($_SESSION["user"]["firstname"]) ?> !</h1>
        <p class="subtitle">Vous êtes connecté.</p>
        <?php if ($managersErrors != ""): ?>
            <p class="alert alert-error"><?= $managersErrors ?></p>
        <?php elseif ($managers === []): ?>
            <p class="empty">Aucun manager pour le moment</p>
        <?php else: ?>
            <table>
                <thead>
                <tr>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Inscrit le</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($managers as $manager): ?>
                    <tr>
                        <td><?= htmlspecialchars($manager["firstname"]) ?></td>
                        <td><?= htmlspecialchars($manager["lastname"]) ?></td>
                        <td><?= htmlspecialchars($manager["email"]) ?></td>
                        <td><?= $manager["created_at"] ? date("d/m/Y", strtotime($manager["created_at"])) : "—" ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <form method="post" action="profile.php" class="inline">
            <input type="submit" name="profil" value="profil" class="btn-secondary">
        </form>
        <form method="post" action="agents.php" class="inline">
            <input type="submit" name="agents" value="Listes des agents" class="btn-secondary">
        </form>
        <form method="post" action="signout.php" class="inline">
            <input type="submit" name="signout" value="Sign Out" class="btn-danger">
        </form>

    <?php else: ?>

        <fieldset class="auth">
            <legend>Register</legend>
            <form action="" method="post">
                <label for="firstname">Firstname</label>
                <input type="text" name="firstname" id="firstname" placeholder="Michel"/>
                <label for="lastname">Lastname</label>
                <input type="text" name="lastname" id="lastname" placeholder="Michel"/>
                <label for="email">E-mail</label>
                <input type="email" name="email" id="email" placeholder="Saisir l'email"/>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="*******"/>
                <label for="confirm-password">Confirm password</label>
                <input type="password" name="confirm-password" id="confirm-password" placeholder="*******"/>
                <input type="submit" name="submit" value="S'inscrire">
            </form>
        </fieldset>

        <?php foreach ($errors as $error): ?>
            <p class="alert alert-error"><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>

        <?php if ($success): ?>
            <p class="alert alert-success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <fieldset class="auth">
            <legend>Login</legend>
            <form action="" method="post">
                <label for="login-email">E-mail</label>
                <input type="email" name="login-email" id="login-email" placeholder="Saisir l'email"/>
                <label for="login-password">Password</label>
                <input type="password" name="login-password" id="login-password" placeholder="*******"/>
                <input type="submit" name="login" value="Se connecter">
            </form>
        </fieldset>

        <?php foreach ($loginErrors as $loginError): ?>
            <p class="alert alert-success"><?= htmlspecialchars($loginError) ?></p>
        <?php endforeach; ?>

    <?php endif; ?>

</main>
</body>
</html>