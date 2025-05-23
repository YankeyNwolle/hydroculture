<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'advertising_db'; // adaptez ici
$username = 'your_username'; // adaptez ici
$password = 'your_password'; // adaptez ici

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Gestion de la soumission du formulaire de réservation
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['panel_id'], $_POST['client_name'], $_POST['client_email'])) {
    $panel_id = (int) $_POST['panel_id'];
    $client_name = trim($_POST['client_name']);
    $client_email = trim($_POST['client_email']);

    if ($client_name === '' || $client_email === '') {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs.</div>';
    } else {
        // Vérifier si le panneau est disponible
        $stmt = $pdo->prepare("SELECT * FROM panels WHERE id = ? AND status = 'available'");
        $stmt->execute([$panel_id]);
        $panel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($panel) {
            // Insérer une nouvelle réservation (dans la table orders avec type 'reservation')
            $insert = $pdo->prepare("INSERT INTO orders (panel_id, client_name, client_email, status, type, order_date) VALUES (?, ?, ?, 'pending', 'reservation', NOW())");
            $success = $insert->execute([$panel_id, $client_name, $client_email]);

            if ($success) {
                // Mettre à jour le statut du panneau en "reserved"
                $update = $pdo->prepare("UPDATE panels SET status = 'reserved' WHERE id = ?");
                $update->execute([$panel_id]);
                $message = '<div class="alert alert-success">Réservation effectuée avec succès !</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de la création de la réservation.</div>';
            }
        } else {
            $message = '<div class="alert alert-warning">Le panneau sélectionné n\'est pas disponible.</div>';
        }
    }
}

// Récupérer les panneaux disponibles
$stmt = $pdo->query("SELECT * FROM panels WHERE status = 'available'");
$panels = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
