<?php
session_start();
if (!isset($_SESSION["admin"])) {
    header("Location: admin_login.php");
    exit;
}

$host = "localhost";
$dbname = "gestion_panneaux";
$username = "root";
$password = "TON_MOT_DE_PASSE";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les données
$panneaux = $pdo->query("SELECT * FROM panneaux")->fetchAll();
$reservations = $pdo->query("SELECT * FROM reservation")->fetchAll();
$incidents = $pdo->query("SELECT * FROM incident")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord Administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Tableau de Bord Administrateur</h1>
        <a href="admin_logout.php" class="btn btn-danger">Déconnexion</a>

        <h2 class="mt-4">Gestion des Panneaux Publicitaires</h2>
        <a href="admin_panneaux.php" class="btn btn-success">Gérer les panneaux</a>

        <h2 class="mt-4">Réservations en cours</h2>
        <ul>
            <?php foreach ($reservations as $r) : ?>
                <li><?= htmlspecialchars($r["client_id"]) ?> a réservé le panneau <?= htmlspecialchars($r["panneau_id"]) ?> jusqu'au <?= htmlspecialchars($r["date_fin"]) ?></li>
            <?php endforeach; ?>
        </ul>

        <h2 class="mt-4">Incidents Signalés</h2>
        <ul>
            <?php foreach ($incidents as $i) : ?>
                <li>Panneau <?= htmlspecialchars($i["panneau_id"]) ?> : <?= htmlspecialchars($i["description"]) ?> (<?= htmlspecialchars($i["date_incident"]) ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
