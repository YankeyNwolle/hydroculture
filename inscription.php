<?php
$pdo = new PDO("mysql:host=localhost;dbname=panneaux;charset=utf8", "root", "");

// Récupérer les données du formulaire
$prenom = $_POST['prenom'] ?? '';
$nom = $_POST['nom'] ?? '';
$telephone = $_POST['telephone'] ?? '';
$email = $_POST['email'] ?? '';
$mot_de_passe = $_POST['mot_de_passe'] ?? '';
$role = $_POST['role'] ?? 'client';

// Vérifier que tous les champs sont remplis
if (!$prenom || !$nom || !$telephone || !$email || !$mot_de_passe) {
    echo "Veuillez remplir tous les champs.";
    exit;
}

// Vérifier si l'email existe déjà
$check = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
$check->execute([$email]);
if ($check->fetch()) {
    echo "Cet email est déjà utilisé.";
    exit;
}

// Sécurité : hasher le mot de passe
$mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

// Insertion
$stmt = $pdo->prepare("INSERT INTO utilisateurs (prenom, nom, telephone, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?, ?)");
$ok = $stmt->execute([$prenom, $nom, $telephone, $email, $mot_de_passe_hash, $role]);

if ($ok) {
    // Rediriger vers la page de connexion avec un message de succès
    header('Location: connexion.php?inscription=success');
    exit;
} else {
    echo "Erreur lors de l'inscription.";
}
?>