<?php

session_start();

require_once "database.php";

require_once "auth.php";

require_manager();

$archetypes = [];
$tiers = [];
$teams = [];
$agents = [];
$agentErrors = [];
$agentSuccess = "";

try {
    $bdd = connect_bdd();
    $stmt = $bdd->prepare("SELECT id_archetype,archetype FROM archetypes ORDER BY archetype");
    $stmt->execute();
    $archetypes = $stmt->fetchAll();
    $stmt = $bdd->prepare("SELECT id_tier,tier,level FROM tiers ORDER BY level");
    $stmt->execute();
    $tiers = $stmt->fetchAll();
    $stmt = $bdd->prepare("SELECT id_team,team_name FROM teams WHERE id_user = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $teams = $stmt->fetchAll();

} catch (PDOException $e) {
    $agentErrors[] = $e->getMessage();
}

if (isset($_POST['add_agent'])) {
    $name = trim($_POST['name'] ?? "");
    $dob = $_POST['dob'] ?? "";
    $idArchetype = $_POST['archetype'] ?? "";
    $idTiers = $_POST['tiers'] ?? "";
    $idEquipe = $_POST['equipe'] ?? "";
    if (empty($name) || empty($dob) || empty($idArchetype) || empty($idTiers)) {
        $agentErrors[] = "Veuillez remplir tous les champs";
    }
    if (mb_strlen($name) > 50){
        $agentErrors[] = "Titre de l'agent est trop long";
    }
    $date = DateTime::createFromFormat('Y-m-d', $dob);

    if (!$date) {
        $agentErrors[] = "Date de naissance illisible";
    } elseif ($date->format('Y-m-d') != $dob) {
        $agentErrors[] = "Date de naissance invalide";
    } elseif ($date > new DateTime()) {
        $agentErrors[] = "Date de naissance dans le futur";
    }

    if ($idEquipe == "") {
        $idEquipe = null;
    } elseif (!in_array($idEquipe,array_column($teams, 'id_team'))) {
        $agentErrors[] = "equipe invalide";
    }

    if (!in_array($idArchetype,array_column($archetypes, 'id_archetype'))) {
        $agentErrors[] = "L'archetype n'existe pas";
    }
    if (!in_array($idTiers,array_column($tiers, 'id_tier'))) {
        $agentErrors[] = "Le tiers n'existe pas";
    }
    if (!$agentErrors) {
        try {
            $bdd = connect_bdd();
            $stmt = $bdd->prepare("INSERT INTO agents (name, dob, created_at, Id_Archetype, Id_Tier, Id_Team) VALUES (?,?,NOW(),?,?,?)");
            $stmt->execute([$name, $dob, $idArchetype, $idTiers, $idEquipe]);
            $agentId = $bdd->lastInsertId();
            header("Location: agents.php?success=1");
            exit();
        } catch (PDOException $e) {
            $agentErrors[] = $e->getMessage();
        }
    }
}

if (isset($_GET['success'])) {
    $agentSuccess = 'Agent ajouter';
}

try {
    $bdd = connect_bdd();
    $stmt = $bdd->prepare("SELECT ag.name, ag.dob, arc.archetype, t.tier, tm.team_name, GROUP_CONCAT(ab.ability SEPARATOR ', ') AS abilities 
FROM agents ag JOIN archetypes arc ON ag.Id_Archetype = arc.Id_Archetype 
    JOIN tiers t ON ag.Id_Tier = t.Id_Tier 
    LEFT JOIN teams tm ON ag.Id_Team = tm.Id_Team 
    LEFT JOIN archetypes_abilities arc_ab ON ag.Id_Archetype = arc_ab.Id_Archetype
    LEFT JOIN abilities ab ON arc_ab.Id_Ability = ab.Id_Ability GROUP BY ag.Id_Agent ORDER BY ag.name");
    $stmt->execute();
    $agents = $stmt->fetchAll();
} catch (PDOException $e) {
    $agentErrors[] = $e->getMessage();
}

?>

<html lang="fr">
<head>
    <link rel="stylesheet" href="style.css">
    <title>Agents</title>
</head>
<?php foreach ($agentErrors as $error): ?>

<p class="alert alert-error"><?= $error ?></p>

<?php endforeach;?>

<?php if ($agentSuccess !== ""): ?>

<p class="alert alert-success"><?= $agentSuccess ?></p>

<?php endif;?>

<form action="" method="post" class="form-grid">
    <label for="name">Name:</label>
    <input type="text" name="name" id="name" maxlength="50" placeholder="name">
    <label for="dob">Dob:</label>
    <input type="date" name="dob" id="dob">
    <label for="archetype">Archetype</label><select name="archetype" id="archetype">
        <option value="">Aucun archetype</option>
        <?php foreach ($archetypes as $archetype) : ?>
        <option value="<?= $archetype["id_archetype"] ?>"><?= htmlspecialchars($archetype["archetype"]) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="tiers">Tier:</label><select name="tiers" id="tiers">
        <option value="">Aucune tier</option>
        <?php foreach ($tiers as $tier) : ?>
            <option value="<?= $tier["id_tier"] ?>"><?= htmlspecialchars($tier["tier"]) ?></option>
        <?php endforeach; ?>
    </select>
    <label for="equipe">Equipe</label><select name="equipe" id="equipe">
        <option value="">Aucune équipe</option>
        <?php foreach ($teams as $team) : ?>
            <option value="<?= $team['id_team']?>"><?= htmlspecialchars($team["team_name"]) ?></option>
        <?php endforeach; ?>
    </select>
    <input type="submit" name="add_agent" value="Ajouter">
</form>
<p><a href="index.php" class="back">Retour à l'accueil</a></p>
<h2>Liste des agents</h2>
<?php if (!$agents): ?>
<p class="empty">Aucun agents</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Date de naissance</th>
            <th>Archétype</th>
            <th>Tier</th>
            <th>Equipe</th>
            <th>Capacités</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($agents as $agent) : ?>
        <tr>
            <td><?= htmlspecialchars($agent['name'])?></td>
            <td><?= htmlspecialchars(date("d/m/Y",strtotime($agent['dob'])))?></td>
            <td><?= htmlspecialchars($agent['archetype'])?></td>
            <td><?= htmlspecialchars($agent['tier'])?></td>
            <td><?= htmlspecialchars($agent['team_name'] ?? "Aucune")?></td>
            <td><?= htmlspecialchars($agent['abilities'] ?? "Aucune")?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>


</html>
