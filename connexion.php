<?php
// Paramètres de connexion
$host = 'localhost'; // ou l'adresse de votre serveur de base de données
$dbname = 'advertising_db'; // nom de votre base de données
$username = 'username'; // votre nom d'utilisateur
$password = 'password'; // votre mot de passe

try {
    // Création d'une nouvelle instance PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    // Définir le mode d'erreur de PDO sur Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie !";
} catch (PDOException $e) {
    echo "Échec de la connexion : " . $e->getMessage();
}
?>
