<?php
// Paramètres de connexion
$host = 'localhost';
$dbname = 'panneaux'; // nom de ta base de données réelle
$username = 'root'; // utilisateur par défaut sur WAMP/XAMPP
$password = ''; // mot de passe vide par défaut

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie !";
} catch (PDOException $e) {
    echo "Échec de la connexion : " . $e->getMessage();
}
?>