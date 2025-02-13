<?php
session_start();

if(!isset($_SESSION['pseudo'])) {
    die('L\'utilisateur n\'est pas connecté');
}

$clientId = $_SESSION['id'];

include ('../../model/bdd.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT p.id, p.en_vente, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.prix, p.matiere_s, p.couleur_s,
        c.titre, g.genre,
    (SELECT GROUP_CONCAT(CONCAT(ps.matiere, ' ', ps.couleur) ORDER BY ps.id ASC SEPARATOR ' et ') 
     FROM produit_suplement ps 
     WHERE ps.id_produit = p.id) AS pierres_info,
    (SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin
    FROM favoris f
    JOIN produit p ON f.id_produit = p.id
    LEFT JOIN collection c ON c.id = p.id_collection
    LEFT JOIN genre g ON g.id = p.id_genre
    WHERE f.id_client = :id_client
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_client', $clientId);
    $stmt->execute();
    $favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/catalogue.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes favoris | jewelr-e</title>
</head>
<body>
    <?php include ('../include/navbar.php'); ?>

    <?php $favorisValid = array_filter($favoris, function($favori) {
        return !empty($favori['id']) && !empty($favori['type_produit']) && !empty($favori['prix']);
    });
    if(count($favorisValid) > 0) { ?>
    <h1>Mes favoris</h1>
    
        <div class="liste-produits">
            <?php foreach($favoris as $favori):
                if (empty($favori['id']) || empty($favori['type_produit']) || empty($favori['prix'])) {
                    continue;
                }
                $stmtImage = $conn->prepare("SELECT image_chemin FROM produit_image WHERE id_produit = :id_produit LIMIT 1");
                $stmtImage->bindParam(':id_produit', $favori['id']);
                $stmtImage->execute();
                $imageResult = $stmtImage->fetch(PDO::FETCH_ASSOC);
            
                $favori['image_chemin'] = $imageResult ? $imageResult['image_chemin'] : 'default_image.png';
            

                $produitId = $favori['id'];
                $stmt = $conn->prepare("SELECT * FROM produit_taille WHERE id_produit = :id_produit LIMIT 1");
                $stmt->bindParam(':id_produit', $produitId, PDO::PARAM_INT);
                $stmt->execute();
                $premiereTaille = $stmt->fetch(PDO::FETCH_ASSOC);
                $pierresInfo = $favori['pierres_info'];
                ?>

                <div class="produit-item">
                    <img src="<?php echo !empty($favori['image_chemin']) ? htmlspecialchars($favori['image_chemin']) : '../img/boutique/default.png'; ?>" alt="<?php echo htmlspecialchars($favori['image_chemin']); ?>">
                    <div class="produit-desc">
                        <div class="haut-produit">
                            <h2><a href="produit_page.php?id=<?php echo htmlspecialchars($favori['id']); ?>&taille=<?php echo htmlspecialchars($premiereTaille['id']); ?>">
                                <?php echo $favori['type_produit'] . " " . $favori['matiere_p'] . " " . $favori['couleur_p'] . " " . $favori['matiere_s'] . " " . $favori['couleur_s'] . " " . $pierresInfo; ?>
                            </a></h2>
                        </div>
                        <h4><?php echo $favori['prix'] . " €"; ?></h4>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
            <?php } else { ?>
                <div class="aucun-article">
                    <div class="bulle">
                        <i class='bx bxs-info-circle bx-lg'></i>
                        <p>Aucun article n'a été ajouté aux favoris</p>
                        <a href="../main/index.php">Accueil</a>
                    </div>
                </div>
            <?php } ?>
        </div>

    <?php include '../include/footer.php' ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const favorisIcon = document.querySelectorAll('.favorisIcon');

            favorisIcon.forEach(icon => {
                icon.addEventListener('click', function () {
                    const produitId = icon.getAttribute('data-produit-id');
                    fetch('/Jewelre/model/ajouter_favoris.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ id_produit: produitId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            if(data.action === 'removed') {
                                icon.classList.remove('added');
                                icon.classList.add('removing');
                                setTimeout(() => {
                                    icon.parentElement.remove();
                                }, 1000);
                            } else if(data.action === 'added') {
                                icon.classList.add('added');
                                icon.classList.remove('removing');
                            }
                            icon.classList.toggle('added');
                        } else {
                            alert(data.message || 'Une erreur est survenue lors de la modification des favoris');
                        }
                    })
                    .catch(error => console.error('Erreur: ', error));
                });
            });

        })
    </script>
</body>
</html>