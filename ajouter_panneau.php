<?php
$host = 'localhost';
$dbname = 'panneaux';
$username = 'root';
$password = '';
$message = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupérer les catégories pour le select
$stmt = $pdo->query("SELECT id, nom FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emplacement = trim($_POST['emplacement']);
    $taille = trim($_POST['taille']);
    $statut = trim($_POST['statut']);
    $prix = trim($_POST['prix']);
    $categorie_id = (int)$_POST['categorie_id'];
    $image_name = null;

    // Gestion de l'upload de l'image
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = __DIR__ . '/img/';
        if (!is_dir($upload_dir)) mkdir($upload_dir);
        $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // OK
        } else {
            $message = '<div class="alert alert-danger">Erreur lors de l\'upload de l\'image.</div>';
        }
    }

    // Insertion en base
    if (!$message) {
        $stmt = $pdo->prepare("INSERT INTO panneaux (emplacement, taille, statut, prix, categorie_id, image) VALUES (?, ?, ?, ?, ?, ?)");
        $ok = $stmt->execute([$emplacement, $taille, $statut, $prix, $categorie_id, $image_name]);
        if ($ok) {
            $message = '<div class="alert alert-success">Panneau ajouté avec succès !</div>';
        } else {
            $message = '<div class="alert alert-danger">Erreur lors de l\'ajout du panneau.</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un panneau</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container my-5">
    <h2>Ajouter un panneau publicitaire</h2>
    <?php echo $message; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Emplacement</label>
            <input type="text" name="emplacement" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Taille</label>
            <input type="text" name="taille" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Statut</label>
            <select name="statut" class="form-control" required>
                <option value="Disponible">Disponible</option>
                <option value="Réservé">Réservé</option>
            </select>
        </div>
        <div class="form-group">
            <label>Prix (FCFA)</label>
            <input type="number" name="prix" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Catégorie</label>
            <select name="categorie_id" class="form-control" required>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['nom']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Image du panneau</label>
            <input type="file" name="image" class="form-control-file" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-success">Ajouter</button>
    </form>
</div>
</body>
</html>