<?php 
session_start();
if (!isset($_SESSION['entreprise'])) {
    header("Location: login_vendeur.php");
    exit();
}

include ('../../model/bdd.php');

$idVendeur = $_SESSION['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/add_article.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <title>Ajouter une collection | Jewelr-e</title>
</head>
<body>
    <!------------------------ NAVBAR ------------------------>
    <?php include ('../include/navbar.php');?>

    <!------------------------ FORMULAIRE ------------------------>
    <div class="page">
        <div class="categorie">
            <div class="btns">
                <a href="add_bague.php" class="categorie-btn">Bagues</a>
                <a href="add_earrings.php" class="categorie-btn">Boucles d'oreilles</a>
                <a href="add_bracelet.php" class="categorie-btn">Bracelets</a>
                <a href="add_collier.php" class="categorie-btn">Colliers</a>
                <a href="add_collection.php" class="categorie-btn active">Collection</a>
            </div>
            <p>*Champs obligatoires</p>
        </div>
        <div class="formulaire">
            <form action="add_collection.php" method="POST" enctype="multipart/form-data">
                <?php 
                include ('../../model/ajouter_collection.php');
                
                if (isset($_SESSION['erreur'])) {
                    echo '<p id="erreur">' . $_SESSION['erreur'] . '</p>';
                    unset($_SESSION['erreur']);
                }
                if (isset($_SESSION['succes'])) {
                    echo '<p id="succes">' . $_SESSION['succes'] . '</p>';
                    unset($_SESSION['succes']);
                } 
                ?>

                <div class="champs">
                    <div class="caracteristiques">
                        <h3>Caractéristiques</h3>
                        <div class="input-group">
                            <label for="genre">Titre*</label> 
                            <input type="text" name="titre" required>
                        </div>
                        <div class="input-group">
                            <label for="genre">Date de sortie*</label> 
                            <input type="date" name="date_sortie" required>
                        </div>
                        <div class="input-group file">
                            <label for="image">Image*</label>
                            <input type="file" name="image" accept=".png, .jpg, .jpeg, .webp, .avif" required>
                        </div>
                        <p id="info">INFO: Il est nécessaire d'ajouter au minimum un article à la collection si vous souhaitez la mettre en vente immédiatement.
                            Elle peut être mis en vente ultérieurement via les stocks.
                        </p>
                        <div class="conditions">
                            <div class="condi">
                                <input type="checkbox" name="envente">
                                <p>Je souhaite mettre en vente cette collection</p>
                            </div>
                            <div class="condi">
                                <input type="checkbox" name="engagement">
                                <p>Je m'engage à fournir des articles conformes aux critères individuels des produits*</p>
                            </div>
                        </div>
                        <div class="btn-submit">
                            <input type="submit" value="AJOUTER">
                        </div>
                    </div>
                    <div class="articles-collection">
                        <h3>Articles de la collection</h3>
                        <?php
                        $sql = "SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p,
                         (SELECT GROUP_CONCAT(CONCAT(ps.matiere, ' ', ps.couleur) ORDER BY ps.id ASC SEPARATOR ' et ') 
                         FROM produit_suplement ps WHERE ps.id_produit = p.id) AS pierres_info,
                         (SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin
                         FROM produit p WHERE p.id_fournisseur = :id_fournisseur AND p.id_collection IS NULL ORDER BY id DESC";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':id_fournisseur', $idVendeur);
                        $stmt->execute();
                        $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        if($produits) { ?> 
                            <div class="window">
                                <?php
                                foreach($produits as $produit) {
                                    $pierresInfo = $produit['pierres_info'];
                                    $stmt = $conn->prepare("SELECT * FROM produit_taille WHERE id_produit = :id_produit LIMIT 1");
                                    $stmt->bindParam(':id_produit', $produit['id']);
                                    $stmt->execute();
                                    $premiereTaille = $stmt->fetch(PDO::FETCH_ASSOC);
                                    ?>

                                    <div class="item-group">
                                        <div class="left">
                                            <img src="<?php echo !empty($produit['image_chemin']) ? htmlspecialchars($produit['image_chemin']) : '../img/boutique/default.png'; ?>" alt="">
                                            <label for="produitItem"><a href="../boutique/produit_page.php?id=<?php echo htmlspecialchars($produit['id']); ?>&taille=<?php echo htmlspecialchars($premiereTaille['id']); ?>"><?php echo $produit['type_produit'] ." ". $produit['motif'] ." ". $produit['matiere_p'] ." ". $produit['couleur_p'] ." ". $pierresInfo; ?></a></label>
                                        </div>
                                        <input type="checkbox" name="produitItem[]" value="<?php echo $produit['id']; ?>">
                                    </div>
                                <?php } ?> 
                            </div> 
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!------------------------ FOOTER ------------------------>
    <?php include ('../include/footer.php');?>
</body>
</html>