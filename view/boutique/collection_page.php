<?php
// Session et connexion a la BDD
session_start();
include ('../../model/bdd.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

// Appel des infos avec l'id en GET
$collectionId = $_GET['id'] ?? '';
$stmt = $conn->prepare("SELECT * FROM collection WHERE id = :id");
$stmt->bindParam(':id', $collectionId);
$stmt->execute();
$collection = $stmt->fetch(PDO::FETCH_ASSOC);
$dateFr = DateTime::createFromFormat('Y-m-d', $collection['date_sortie'])->format('d/m/Y');

if($collection) {
    $stmt = $conn->prepare("SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.matiere_s, p.couleur_s, p.prix,
       GROUP_CONCAT(CONCAT(ps.matiere, ' ', ps.couleur) ORDER BY ps.id ASC SEPARATOR ' et ') AS pierres_info,
       (SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin
       FROM produit p LEFT JOIN produit_suplement ps ON ps.id_produit = p.id WHERE p.id_collection = :id GROUP BY p.id ORDER BY RAND()");
    $stmt->bindParam(':id', $collectionId);
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/collection_page.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Titre dynamique -->
    <title>
        <?php if(empty($collection)) {
            echo "Collection introuvable | Jewelr-e";
        } else {
            echo "Collection " . $collection['titre'] . " | Jewelr-e";
        } ?>
    </title>
</head>
<body>
    <!-- Navbar -->
    <?php include ('../include/navbar.php'); ?>
    
    <!-- Bouton retour -->
    <div class="retour">
        <button onclick="history.back();" id="btnRetour">< Retour</button>
    </div>

    <!-- Collection -->
    <div class="collection">
        <!-- Image de la collection -->
        <div class="collection-image">
            <img src="<?php echo !empty($collection['image_chemin']) ? $collection['image_chemin'] : '../img/collection/default.png'; ?>" alt="Photo principale de la collection <?php echo $collection['titre']; ?>">
        </div>
        <!-- Contenu collection -->
        <div class="collection-content">
            <!-- Titre -->
            <div class="title">
                <h2>Collection <?php echo $collection['titre']; ?></h2>
                <p id="date"><?php echo $dateFr; ?></p>
            </div>

            <!-- Liste des produits -->
            <div class="produits">
                <?php 
                $nombreArticles = count($produits);
                if($nombreArticles == 0) {
                    echo "<h4>Aucun produit n'est disponible dans cette collection</h4>";
                } else if($nombreArticles == 1) {
                    echo "<h4>1 produit est disponible dans cette collection</h4>";
                } else {
                    echo "<h4>". $nombreArticles . " produits sont disponibles dans cette collection</h4>";
                }
                ?>
                
                <div class="carousel-container">
                    <button class="prev">&#10094;</button>
                    <div class="carousel">
                        <?php foreach($produits as $produit) { 
                            $pierresInfo = $produit['pierres_info'];
                            $produitId = $produit['id'];
                            $stmt = $conn->prepare("SELECT * FROM produit_taille WHERE id_produit = :id_produit LIMIT 1");
                            $stmt->bindParam(':id_produit', $produitId, PDO::PARAM_INT);
                            $stmt->execute();
                            $premiereTaille = $stmt->fetch(PDO::FETCH_ASSOC);
                            ?>

                            <!-- Item individuel -->
                            <div class="produit-item">
                                <img src="<?php echo !empty($produit['image_chemin']) ? htmlspecialchars($produit['image_chemin']) : '../img/boutique/default.png'; ?>" alt="<?php echo htmlspecialchars($produit['image_chemin']); ?>">
                                <div class="produit-desc">
                                    <div class="haut-produit">
                                        <h2><a href="produit_page.php?id=<?php echo htmlspecialchars($produit['id']); ?>&taille=<?php echo htmlspecialchars($premiereTaille['id']); ?>">
                                            <?php echo $produit['type_produit'] . " " . $produit['motif'] . " " . $produit['matiere_p'] . " " . $produit['couleur_p'] . " " . $produit['matiere_s'] . " " . $produit['couleur_s'] . " " . $pierresInfo; ?>
                                        </a></h2>
                                    </div>
                                    <h4><?php echo $produit['prix'] . " €"; ?></h4>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                    <button class="next">&#10095;</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include ('../include/footer.php'); ?>

    <!-- JS -->
    <script>
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const carousel = document.querySelector('.carousel');
    const itemWidth = document.querySelector('.produit-item').offsetWidth + 15;
    const totalItems = document.querySelectorAll('.produit-item').length;
    const visibleItems = 4;
    let currentPosition = 0;

    function scrollLeft() {
        if (currentPosition > 0) {
            currentPosition -= itemWidth;
            carousel.style.transform = `translateX(-${currentPosition}px)`;
        }
    }

    function scrollRight() {
        if (currentPosition < (totalItems - visibleItems) * itemWidth) {
            currentPosition += itemWidth;
            carousel.style.transform = `translateX(-${currentPosition}px)`;
        }
    }

    prevBtn.addEventListener('click', scrollLeft);
    nextBtn.addEventListener('click', scrollRight);
</script>

</body>
</html>