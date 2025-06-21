<?php 
session_start(); 
include ('../../model/bdd.php');

if(isset($_GET['query'])) {
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        exit();
    }

    $stmt = $conn->prepare(
        "SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.prix, p.matiere_s, p.couleur_s,c.titre, g.genre,
       GROUP_CONCAT(
           CONCAT(ps.matiere, ' ', ps.couleur) 
           ORDER BY ps.id ASC SEPARATOR ' et '
       ) AS pierres_info,
       (SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin
        FROM produit p
        LEFT JOIN produit_suplement ps ON ps.id_produit = p.id
        LEFT JOIN collection c ON c.id = p.id_collection
        LEFT JOIN genre g ON g.id = p.id_genre
        WHERE (p.type_produit LIKE :query 
         OR p.motif LIKE :query 
         OR p.matiere_p LIKE :query 
         OR p.couleur_p LIKE :query 
         OR p.matiere_s LIKE :query 
         OR p.couleur_s LIKE :query 
         OR c.titre LIKE :query
         OR ps.matiere LIKE :query
         OR ps.couleur LIKE :query
         OR ps.forme LIKE :query
         OR CONCAT_WS(' ', p.type_produit, p.matiere_p, p.couleur_p, p.motif, ps.matiere, ps.couleur) LIKE :query)
        AND p.en_vente = 1
        GROUP BY p.id ORDER BY RAND()
    ");
    $stmt->execute(['query' => '%' . $_GET['query'] . '%']);
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header("Location: /Jewelre/view/main/index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/catalogue.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <title>Résultats | Jewelr-e</title>
</head>
<body>
    <?php include ('../include/navbar.php'); ?>

    <?php if(!empty($produits) && isset($_GET{'query'})) { ?>
        <div class="recherche-query">
            <h4>Résultats pour "<?php echo $_GET['query'] ?>"</h4>
        </div>
        <div class="liste-produits">
                <?php foreach($produits as $produit): 
                    $produitId = $produit['id'];
                    $stmt = $conn->prepare("SELECT * FROM produit_taille WHERE id_produit = :id_produit LIMIT 1");
                    $stmt->bindParam(':id_produit', $produitId, PDO::PARAM_INT);
                    $stmt->execute();
                    $premiereTaille = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    $pierresInfo = $produit['pierres_info'];
                    ?>

                    <div class="produit-item">
                        <img src="<?php echo !empty($produit['image_chemin']) ? htmlspecialchars($produit['image_chemin']) : '../img/boutique/default.png'; ?>" alt="<?php echo htmlspecialchars($produit['image_chemin']); ?>">
                        <div class="produit-desc">
                            <div class="haut-produit">
                                <h2><a href="produit_page.php?id=<?php echo htmlspecialchars($produit['id']); ?>&taille=<?php echo htmlspecialchars($premiereTaille['id']); ?>"><?php echo $produit['type_produit'] ." ". $produit['titre'] ." ". $produit['matiere_p'] ." ". $produit['couleur_p'] ." ". $produit['matiere_s'] ." ". $produit['couleur_s'] ." ". $pierresInfo; ?></a></h2>
                                <form method="POST">
                                    <input type="hidden" name="idProduit" value="<?php echo htmlspecialchars($produitId) ;?>">
                                    <button type="submit" name="ajouterAuxFavoris"><i class='bx bx-heart'></i></button>
                                </form>
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

    <?php include ('../include/footer.php'); ?>
</body>
</html>