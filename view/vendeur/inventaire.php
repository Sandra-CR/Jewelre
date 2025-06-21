<?php 
session_start(); 
if (!isset($_SESSION['entreprise'])) {
    header("Location: /Jewelre/view/main/login_vendeur.php");
    exit();
}

include ('../../model/bdd.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $prixQuery = $conn->query("SELECT MIN(prix) AS prix_min, MAX(prix) AS prix_max FROM produit");
    $prixRange = $prixQuery->fetch(PDO::FETCH_ASSOC);
    $prixMin = floor($prixRange['prix_min']) ?? 0;
    $prixMax = ceil($prixRange['prix_max']) ?? 200.00;

    $filtres = [];
    $sql = "
     SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.prix, p.matiere_s, p.couleur_s,c.titre, g.genre,
       -- Utilisation de GROUP_CONCAT pour combiner les informations des pierres
       GROUP_CONCAT(
           CONCAT(ps.matiere, ' ', ps.couleur) 
           ORDER BY ps.id ASC SEPARATOR ' et '
       ) AS pierres_info,
       (SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin
     FROM produit p
     LEFT JOIN produit_suplement ps ON ps.id_produit = p.id
     LEFT JOIN collection c ON c.id = p.id_collection
     LEFT JOIN genre g ON g.id = p.id_genre
     WHERE p.id_fournisseur = :id
";

    $filtreConditions = [];
    if(isset($_GET['filtre_bague'])) {
        $filtreConditions[] = "p.type_produit = 'Bague'";
    }
    if(isset($_GET['filtre_earrings'])) {
        $filtreConditions[] = "p.type_produit = 'Boucles d\'oreilles'";
    }
    if(isset($_GET['filtre_bracelet'])) {
        $filtreConditions[] = "p.type_produit = 'Bracelet'";
    }
    if(isset($_GET['filtre_collier'])) {
        $filtreConditions[] = "p.type_produit = 'Collier'";
    }
    if(isset($_GET['filtre_collection'])) {
        $filtreConditions[] = "p.type_produit = 'Collection'";
    }

    $filtreGenres = [];
    if(isset($_GET['filtre_homme'])) {
        $filtreGenres[] = "g.genre = 'Homme'";
    }
    if(isset($_GET['filtre_femme'])) {
        $filtreGenres[] = "g.genre = 'Femme'";
    }

    $filtreStatusVente = [];
    if(isset($_GET['filtre_horsvente'])) {
        $filtreStatusVente[] = "p.en_vente = 0";
    }
    if(isset($_GET['filtre_envente'])) {
        $filtreStatusVente[] = "P.en_vente = 1";
    }

    if (count($filtreConditions) > 0) {
        $sql .= " AND (" . implode(" OR ", $filtreConditions) . ")";
    }

    if(isset($_GET['prix_min']) && isset($_GET['prix_max'])) {
        $min_prix = $_GET['prix_min'];
        $max_prix = $_GET['prix_max'];
        $sql .= " AND (p.prix BETWEEN $min_prix AND $max_prix)";
    }

    if (count($filtreGenres) > 0) {
        $sql .= " AND (" . implode(" OR ", $filtreGenres) . ")";
    }

    if (count($filtreStatusVente) > 0) {
        $sql .= " AND (" . implode(" OR ", $filtreStatusVente) . ")";
    }

    $sql .= " GROUP BY p.id ORDER BY RAND()";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $_SESSION['id']);
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/catalogue.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon inventaire | Jewelr-e</title>
</head>
<body>
    <?php include ('../include/navbar.php'); ?>
    <div class="page-content">
        <form method="GET" id="filtresForm">
            <div class="filtres">
                <h3>Catégories</h3>
                <div class="categories">
                    <div class="filtre-group">
                        <input type="checkbox" name="filtre_bague" <?php echo isset($_GET['filtre_bague']) ? 'checked' : ''; ?>>
                        <label for="filtre_bague">Bague</label>
                    </div>
                    <div class="filtre-group">
                        <input type="checkbox" name="filtre_earrings" <?php echo isset($_GET['filtre_earrings']) ? 'checked' : ''; ?>>
                        <label for="filtre_earrings">Boucles d'oreilles</label>
                    </div>
                    <div class="filtre-group">
                        <input type="checkbox" name="filtre_bracelet" <?php echo isset($_GET['filtre_bracelet']) ? 'checked' : ''; ?>>
                        <label for="filtre_bracelet">Bracelet</label>
                    </div>
                    <div class="filtre-group">
                        <input type="checkbox" name="filtre_collier" <?php echo isset($_GET['filtre_collier']) ? 'checked' : ''; ?>>
                        <label for="filtre_collier">Collier</label>
                    </div>
                    <div class="filtre-group">
                        <input type="checkbox" name="filtre_collection" <?php echo isset($_GET['filtre_collection']) ? 'checked' : ''; ?>>
                        <label for="filtre_collection">Collection</label>
                    </div>
                </div>
                <div class="line"></div>
                <h3>Prix</h3>
                <div class="prix">
                    <div class="prix-slider">
                        <div class="slider-group">
                            <label for="prix_min">Min </label>
                            <input type="range" name="prix_min" min="<?php echo $prixMin;?>" max="<?php echo $prixMax;?>" value="<?php echo isset($_GET['prix_min']) ? $_GET['prix_min'] : $prixMin; ?>" step="1" id="minPrix" class="range-input">
                        </div>
                        <div class="slider-group">
                            <label for="prix_max">Max </label>
                            <input type="range" name="prix_max" min="<?php echo $prixMin;?>" max="<?php echo $prixMax;?>" value="<?php echo isset($_GET['prix_max']) ? $_GET['prix_max'] : $prixMax; ?>" step="1" id="maxPrix" class="range-input">
                        </div>
                    </div>
                    <div class="prix-etablis">
                        <p><span id="minPrixLabel"><?php echo isset($_GET['prix_min']) ? $_GET['prix_min'] : $prixMin; ?></span>€ - 
                        <span id="maxPrixLabel"><?php echo isset($_GET['prix_max']) ? $_GET['prix_max'] : $prixMax; ?></span>€</p>
                    </div>
                </div>
                <div class="line"></div>
                <h3>Genres</h3>
                <div class="categories">
                    <div class="filtre-group">
                        <input type="checkbox" name="filtre_femme" <?php echo isset($_GET['filtre_femme']) ? 'checked' : ''; ?>>
                        <label for="filtre_femme">Femme</label>
                    </div>
                    <div class="filtre-group">
                        <input type="checkbox" name="filtre_homme" <?php echo isset($_GET['filtre_homme']) ? 'checked' : ''; ?>>
                        <label for="filtre_homme">Homme</label>
                    </div>
                </div>
                <div class="line"></div>
                <h3>Status</h3>
                <div class="categories">
                    <div class="filtre-group">
                        <input type="checkbox" name="filtre_envente" <?php echo isset($_GET['filtre_envente']) ? 'checked' : ''; ?>>
                        <label for="filtre_envente">En vente</label>
                    </div>
                    <div class="filtre-group">
                        <input type="checkbox" name="filtre_horsvente" <?php echo isset($_GET['filtre_horsvente']) ? 'checked' : ''; ?>>
                        <label for="filtre_horsvente">Hors vente</label>
                    </div>
                </div>
            </div>
        </form>
        <?php if(!empty($produits)) { ?>
            <div class="liste-produits">
                <?php foreach($produits as $produit): 
                    $produitId = $produit['id'];
                    $stmt = $conn->prepare("SELECT * FROM produit_taille WHERE id_produit = :id_produit LIMIT 1");
                    $stmt->bindParam(':id_produit', $produitId, PDO::PARAM_INT);
                    $stmt->execute();
                    $premiereTaille = $stmt->fetch(PDO::FETCH_ASSOC);

                    $pierresInfo = $produit['pierres_info']; ?>
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
                <?php endforeach; ?>
            </div>
        <?php } else { ?>
            <div class="aucun-article">
                <div class="bulle">
                    <i class='bx bxs-info-circle bx-lg'></i>
                    <p>Aucun article disponible</p>
                    <a href="../main/index.php">Accueil</a>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php include ('../include/footer.php'); ?>

<script>
    const minSlider = document.getElementById('minPrix');
    const maxSlider = document.getElementById('maxPrix');
    const minPrixLabel = document.getElementById('minPrixLabel');
    const maxPrixLabel = document.getElementById('maxPrixLabel');
    const filtresForm = document.getElementById('filtresForm');

    if (minSlider && maxSlider && minPrixLabel && maxPrixLabel && filtresForm) {
        minSlider.addEventListener('input', function () {
            const minValue = parseInt(minSlider.value);
            const maxValue = parseInt(maxSlider.value);
            if (minValue > maxValue) {
                maxSlider.value = minValue;
            }
            minPrixLabel.textContent = minSlider.value;
            filtresForm.submit();
        });

        maxSlider.addEventListener('input', function () {
            const maxValue = parseInt(maxSlider.value);
            const minValue = parseInt(minSlider.value);
            if (maxValue < minValue) {
                minSlider.value = maxValue;
            }
            maxPrixLabel.textContent = maxSlider.value;
            filtresForm.submit(); 
        });

        const checkboxes = document.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                filtresForm.submit();
            });
        });
    } else {
        console.error("Certains éléments nécessaires sont manquants dans le DOM.");
    }
</script>
</body>
</html>