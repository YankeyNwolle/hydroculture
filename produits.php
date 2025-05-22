<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$dbname = 'panneaux';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Gestion de la soumission du formulaire de commande
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['panel_id'], $_POST['client_name'], $_POST['client_email'])) {
    $panel_id = (int) $_POST['panel_id'];
    $client_name = trim($_POST['client_name']);
    $client_email = trim($_POST['client_email']);

    if ($client_name === '' || $client_email === '') {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs.</div>';
    } else {
        // Vérifier si le panneau est disponible
        $stmt = $pdo->prepare("SELECT * FROM panneaux WHERE id = ? AND statut = 'Disponible'");
        $stmt->execute([$panel_id]);
        $panel = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($panel) {
            // Insérer une nouvelle commande (adapte la table si besoin)
            $insert = $pdo->prepare("INSERT INTO orders (panel_id, client_name, client_email, status, order_date) VALUES (?, ?, ?, 'pending', NOW())");
            $success = $insert->execute([$panel_id, $client_name, $client_email]);

            if ($success) {
                // Mettre à jour le statut du panneau en "Réservé"
                $update = $pdo->prepare("UPDATE panneaux SET statut = 'Réservé' WHERE id = ?");
                $update->execute([$panel_id]);
                $message = '<div class="alert alert-success">Commande passée avec succès !</div>';
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de la création de la commande.</div>';
            }
        } else {
            $message = '<div class="alert alert-warning">Le panneau sélectionné n\'est pas disponible.</div>';
        }
    }
}

// Récupérer les catégories pour les filtres
$stmt = $pdo->query("SELECT id, nom FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les localisations pour les filtres
$stmt = $pdo->query("SELECT DISTINCT adresse FROM localisation");
$localisations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer les filtres
$where = "WHERE p.statut = 'Disponible'";
$params = [];

if (!empty($_GET['categorie'])) {
    $where .= " AND p.categorie_id = ?";
    $params[] = $_GET['categorie'];
}
if (!empty($_GET['localisation'])) {
    $where .= " AND l.adresse = ?";
    $params[] = $_GET['localisation'];
}
if (!empty($_GET['statut'])) {
    $where .= " AND p.statut = ?";
    $params[] = $_GET['statut'];
}

// Récupérer les panneaux disponibles avec leur catégorie et localisation
$sql = "SELECT p.id, p.emplacement, p.taille, p.statut, p.prix, 
               c.nom AS categorie, 
               l.adresse,
               p.image
        FROM panneaux p
        JOIN categories c ON p.categorie_id = c.id
        LEFT JOIN localisation l ON l.panneau_id = p.id
        $where";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$panels = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE htm>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Hydroculture</title>

    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@500;600;700&family=Open+Sans:wght@400;500&display=swap" rel="stylesheet">  

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
    </div>
    <!-- Spinner End -->


    <!-- Topbar Start -->
    <div class="container-fluid bg-dark text-light px-0 py-2">
        <div class="row gx-0 d-none d-lg-flex">
            <div class="col-lg-7 px-5 text-start">
                <div class="h-100 d-inline-flex align-items-center me-4">
                    <span class="fa fa-phone-alt me-2"></span>
                    <span>+012 345 6789</span>
                </div>
                <div class="h-100 d-inline-flex align-items-center">
                    <span class="far fa-envelope me-2"></span>
                    <span>info@example.com</span>
                </div>
            </div>
            <div class="col-lg-5 px-5 text-end">
                <div class="h-100 d-inline-flex align-items-center mx-n2">
                    <span>Follow Us:</span>
                    <a class="btn btn-link text-light" href=""><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-link text-light" href=""><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-link text-light" href=""><i class="fab fa-linkedin-in"></i></a>
                    <a class="btn btn-link text-light" href=""><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->


    <!-- Navbar Start -->
    <nav class="navbar navbar-expand-lg bg-white navbar-light sticky-top p-0">
        <a href="index.html" class="navbar-brand d-flex align-items-center px-4 px-lg-5">
            <h1 class="m-0" id="title">panneaux publicitaire</h1>
        </a>
        <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto p-4 p-lg-0">
                <a href="index.htm" class="nav-item nav-link">Accueil</a>
                <a href="produits.php" class="nav-item nav-link active">Nos Produits</a>
                <a href="service.htm" class="nav-item nav-link">A propos</a>
                <a href="reservation.html" class="nav-item nav-link">Reservation</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Aide</a>
                    <div class="dropdown-menu bg-light m-0">
                        <a href="assistance.htm" class="dropdown-item">Centre D'assistance</a>
                        <a href="passer_commande.htm" class="dropdown-item">Passer une commande</a>
                        <a href="payer_commande.htm" class="dropdown-item">Payer votre commande</a>
                        <a href="testimonial.htm" class="dropdown-item">Suivre Votre commande</a>
                        <a href="#annuler.htm" class="dropdown-item">Annuler des commandes</a>
                        <a href="#retour.html" class="dropdown-item">Faire un retour</a>
                        <a href="whatsapp.html" class="dropdown-item">WhatsApp en direct</a>
                    </div>
                </div>
                <a href="contact.htm" class="nav-item nav-link">Contact</a>
                <a href="panier.php" class="nav-link position-relative ms-3">
                    <i class="fas fa-shopping-cart fa-lg"></i>
                    <span id="notificationCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        0
                        <span class="visually-hidden">nouveaux articles</span>
                    </span>
                </a>
            </div>

           <div class="connexion">
            <a href="#" class="btn-custom btn-green" id="btnShowLogin">Se connecter</a>
                <button id="btnShowSignup" class="btn-custom btn-blue">S'inscrire</button>
        </div>

            
        </div>
    </nav>
    <!-- Navbar End -->

<!--fin header -->
<div class="container my-5">
    <h1 class="mb-4">Panneaux Publicitaires Disponibles</h1>

    <!-- Filtres -->
    <form class="mb-4" method="GET">
        <div class="row">
            <div class="col-md-3">
                <select name="categorie" class="form-control">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php if(isset($_GET['categorie']) && $_GET['categorie'] == $cat['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['nom'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="localisation" class="form-control">
                    <option value="">Toutes les localisations</option>
                    <?php foreach ($localisations as $loc): ?>
                        <option value="<?php echo htmlspecialchars($loc['adresse'] ?? ''); ?>" <?php if(isset($_GET['localisation']) && $_GET['localisation'] == $loc['adresse']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($loc['adresse'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="statut" class="form-control">
                    <option value="">Tous les statuts</option>
                    <option value="Disponible" <?php if(isset($_GET['statut']) && $_GET['statut'] == 'Disponible') echo 'selected'; ?>>Disponible</option>
                    <option value="Réservé" <?php if(isset($_GET['statut']) && $_GET['statut'] == 'Réservé') echo 'selected'; ?>>Réservé</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </div>
    </form>

    <?php echo $message; ?>

    <?php if (count($panels) === 0): ?>
        <p>Aucun panneau publicitaire disponible actuellement.</p>
    <?php else: ?>
        <div class="row">
        <?php foreach ($panels as $panel): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <?php if (!empty($panel['image'])): ?>
                        <img src="img/<?php echo htmlspecialchars($panel['image'] ?? ''); ?>" class="card-img-top" alt="Panneau">
                    <?php else: ?>
                        <img src="img/default.jpg" class="card-img-top" alt="Panneau">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($panel['emplacement'] ?? ''); ?></h5>
                        <p class="card-text"><strong>Catégorie :</strong> <?php echo htmlspecialchars($panel['categorie'] ?? ''); ?></p>
                        <p class="card-text"><strong>Taille :</strong> <?php echo htmlspecialchars($panel['taille'] ?? ''); ?></p>
                        <p class="card-text"><strong>Adresse :</strong> <?php echo htmlspecialchars($panel['adresse'] ?? ''); ?></p>
                        <p class="card-text"><strong>Statut :</strong> <?php echo htmlspecialchars($panel['statut'] ?? ''); ?></p>
                        <p class="card-text"><strong>Prix :</strong> <?php echo htmlspecialchars($panel['prix'] ?? ''); ?> FCFA</p>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#orderModal"
                                data-panelid="<?php echo $panel['id']; ?>"
                                data-location="<?php echo htmlspecialchars($panel['adresse'] ?? ''); ?>">
                            Commander ce panneau
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal de commande -->
<div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderModalLabel">Passer une commande</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="panel_id" id="panel_id" />
        <div class="form-group">
          <label for="client_name">Votre nom</label>
          <input type="text" name="client_name" id="client_name" class="form-control" required />
        </div>
        <div class="form-group">
          <label for="client_email">Votre email</label>
          <input type="email" name="client_email" id="client_email" class="form-control" required />
        </div>
        <p id="panel_location_info" class="font-weight-bold"></p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Confirmer la commande</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
      </div>
    </form>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Préremplir le formulaire du modal avec les bonnes données du panneau sélectionné
  $('#orderModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var panelId = button.data('panelid');
    var location = button.data('location');

    var modal = $(this);
    modal.find('#panel_id').val(panelId);
    modal.find('#panel_location_info').text('Emplacement sélectionné : ' + location);
  });
    <div class="container-fluid bg-dark text-light footer mt-5 py-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-white mb-4">Notre Bureau</h4>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>123 Rue, Abidjan, Côte d'Ivoire</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+225 0151903892</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>hydroculture.ci</p>
                    <div class="d-flex pt-2">
                        <a class="btn btn-square btn-outline-light rounded-circle me-2" href=""><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-square btn-outline-light rounded-circle me-2" href=""><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-square btn-outline-light rounded-circle me-2" href=""><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-square btn-outline-light rounded-circle me-2" href=""><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-white mb-4">Nos Services</h4>
                    <a class="btn btn-link" href="">Systèmes Hydroponiques</a>
                    <a class="btn btn-link" href="">Gestion des Nutriments</a>
                    <a class="btn btn-link" href="">Jardinage Urbain</a>
                    <a class="btn btn-link" href="">Optimisation des Cultures</a>
                    <a class="btn btn-link" href="">Technologie Verte</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-white mb-4">Liens Rapides</h4>
                    <a class="btn btn-link" href="">À Propos</a>
                    <a class="btn btn-link" href="">Contact</a>
                    <a class="btn btn-link" href="">Nos Services</a>
                    <a class="btn btn-link" href="">Conditions Générales</a>
                    <a class="btn btn-link" href="">Support</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-white mb-4">Newsletter</h4>
                    <p>Recevez les dernières actualités et conseils sur l’hydroculture.</p>
                    <div class="position-relative w-100">
                        <input class="form-control bg-light border-light w-100 py-3 ps-4 pe-5" type="text" placeholder="Votre email">
                        <button type="button" class="btn btn-primary py-2 position-absolute top-0 end-0 mt-2 me-2">S'inscrire</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="container-fluid copyright py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    &copy; <a class="border-bottom" href="#">Hydroculture</a>
                </div>
                
            </div>
        </div>
    </div>


    <div class="container-fluid copyright py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    &copy; <a class="border-bottom" href="#">Hydroculture</a>
                </div>
                
            </div>
        </div>
    </div>


    <a href="#" class="btn btn-lg btn-primary btn-lg-square rounded-circle back-to-top"><i class="bi bi-arrow-up"></i></a>


    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/parallax/parallax.min.js"></script>
    <script src="lib/isotope/isotope.pkgd.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>

    <script src="js/main.js"></script>
</script>
</body>
</html>