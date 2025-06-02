<?php
    session_start();

    include ('../../model/bdd.php');
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        exit();
    }
    
    if (!isset($_SESSION['entreprise'])) {
        header("Location: login_vendeur.php");
        exit();
    } 

    $idVendeur = $_SESSION['id'];

    $stmt = $conn->prepare("SELECT id from produit WHERE id_fournisseur = :id_fournisseur");
    $stmt->bindParam('id_fournisseur', $idVendeur);
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($produits)) {
        $nbVentes = 0;
    } else {
        $placeholders = implode(',', array_fill(0, count($produits), '?'));
        $sqlVentes = "SELECT COUNT(*) FROM commande_contenu WHERE id_produit IN ($placeholders)";
        $stmt = $conn->prepare($sqlVentes);
        $stmt->execute($produits);

        $nbVentes = $stmt->fetchColumn();
    }


    $stmt = $conn->prepare("SELECT COUNT(*) from produit WHERE id_fournisseur = :id_fournisseur");
    $stmt->bindParam('id_fournisseur', $idVendeur);
    $stmt->execute();
    $produitsEnvente = $stmt->fetchColumn();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <title>Tableau de bord | Jewelr-e</title>
</head>
<body>
    <!------------------------ NAVBAR ------------------------>
    <?php include ('../include/navbar.php');?>

    <div class="retour">
            <button onclick="history.back();" id="btnRetour">< Retour</button>
    </div>

    <div class="dashboard-container">
        <div class="item vente">
            
            <?php if($nbVentes == 0) { ?>
                <div class="rang" id="aucun">
                    <?php echo $nbVentes; ?>
                </div>
                <div class="text">
                    <h4>Aucun produit vendu</h4>
                    <p>Vendez votre premier produit</p>
                </div>

            <?php } else if($nbVentes < 10) { ?>
                <div class="rang" id="argent">
                    <?php echo $nbVentes; ?>
                </div>
                <div class="text" id="textA">
                    <h3>Argent</h3>
                    <h4>Produits vendus</h4>
                    <p>Prochain rang dans <?php echo (10 - $nbVentes); ?> ventes</p>
                </div>

            <?php } else if($nbVentes < 50) { ?>
                <div class="rang" id="or">
                    <?php echo $nbVentes; ?>
                </div>
                <div class="text" id="text0">
                    <h3>Or</h3>
                    <h4>Produits vendus</h4>
                    <p>Prochain rang dans <?php echo (50 - $nbVentes); ?> ventes</p>
                </div>

            <?php } else { ?>
                <div class="rang" id="diamant">
                    <?php echo $nbVentes; ?>
                </div>
                <div class="text" id="textD">
                    <h3>Diamant</h3>
                    <h4>Produits vendus</h4>
                    <p>Rang maximal atteint!</p>
                </div>

            <?php } ?>
        </div>

        <div class="item produits">
            <div class="left">
                <i class='bx bxs-box'></i>
                <div class="rang" id="all">
                    <p><?php echo $produitsEnvente; ?></p>
                    <h4>Produits disponibles</h4>
                </div>
                <h6>- 38 en vente</p>
                <h6>- aucun hors vente</p>
            </div>

            <div class="separateur"></div>

            <div class="right">
                <h5>Répartition</h5>
                    <div class="group">
                        <p>Boucles d'oreilles</p>
                        <div class="barre" style="width: 87px;"></div>
                    </div>
                    <div class="group">
                        <p>Bracelets</p>
                        <div class="barre" style="width: 165px;"></div>
                    </div>
                    <div class="group">
                        <p>Colliers</p>
                        <div class="barre" style="width: 200px;"></div>
                    </div>
                    <div class="group">
                        <p>Bagues</p>
                        <div class="barre" style="width: 229px;"></div>
                    </div>
            </div>
        </div>
    </div>
    <div class="bottom-container">
        <div class="button1"><a href="inventaire.php">Consulter les commandes</a><i class='bx bx-right-arrow-alt'></i></div>
        <div class="button2"><a href="#">Consulter les stocks</a><i class='bx bx-right-arrow-alt'></i></div>
    </div>

    <!------------------------ FOOTER ------------------------>
    <?php include ('../include/footer.php');?>
</body>
</html>