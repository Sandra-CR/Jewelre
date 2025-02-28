<?php 
session_start(); 

include ('../../model/bdd.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT * FROM collection WHERE en_vente = 1 ORDER BY RAND()";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $collections = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Collections | Jewelr-e</title>
</head>
<body>
    <?php include ('../include/navbar.php'); ?>
    <div class="page-content">
        <?php if(!empty($collections)) { ?>
            <div class="order-buttons">
                <!-- Ajouter des boutons qui font du order by -->
            </div>
            <div class="liste-collections">
                <?php 
                foreach($collections as $collection): 
                    $collectionId = $collection['id'];

                    $stmt = $conn->prepare("SELECT entreprise FROM fournisseur WHERE id = :id");
                    $stmt->bindParam(':id', $collection['id_fournisseur']);
                    $stmt->execute();
                    $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>

                    <div class="collection-item">
                        <img src="<?php echo !empty($collection['image_chemin']) ? htmlspecialchars($collection['image_chemin']) : '../img/collection/default.png'; ?>" alt="<?php echo htmlspecialchars($collection['image_chemin']); ?>">
                        <div class="collection-desc">
                            <div class="haut-collection">
                                <h2><a href="collection_page.php?id=<?php echo $collectionId; ?>">
                                    <?php echo $collection['titre']; ?>
                                </a></h2>
                                <div class="bottom">
                                    <div class="arrow-to-page">
                                        <a href="collection_page.php?id=<?php echo $collectionId; ?>"><i class='bx bx-right-arrow-alt'></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php } else { ?>
            <div class="aucun-article">
                <div class="bulle">
                    <i class='bx bxs-info-circle bx-lg'></i>
                    <p>Aucune collection disponible</p>
                    <a href="../main/index.php">Accueil</a>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php include ('../include/footer.php'); ?>
</body>
</html>