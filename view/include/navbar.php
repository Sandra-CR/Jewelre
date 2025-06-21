<?php 
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../../model/bdd.php');

$favoris_exist = false;

if(isset($_SESSION['pseudo'])) {
    try{
        if(!isset($conn)) {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        $stmt = $conn->prepare("SELECT COUNT(*) FROM favoris WHERE id_client = :id_client");
        $stmt->bindParam(':id_client', $_SESSION['id']);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if($count > 0) {
            $favoris_exist = true;
        }

        if(isset($_SESSION['panier']) && is_array($_SESSION['panier']) && !empty($_SESSION['panier'])) {
            $nombreItemsPanier = count($_SESSION['panier']);
            $nombreItemsPanierAffichage = $nombreItemsPanier > 99 ? '99+' : $nombreItemsPanier;
            $panier_exist = true;
        } else {
            $panier_exist = false;
            $nombreItemsPanierAffichage = 0;
        }
    } catch(PDOException $e) {
        error_log("Erreur : " . $e->getMessage());
    }

    $stmt = $conn->prepare("SELECT COUNT(*) AS total_favoris FROM favoris WHERE id_client = :id_client");
    $stmt->bindParam(':id_client', $_SESSION['id']);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalFavoris = $result['total_favoris'];

    $nombreFavoris = $totalFavoris > 99 ? '99+' : $totalFavoris;

}
?>

<!---------------------- LINKS ----------------------->
<head>
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<!---------------------- NAVBAR ----------------------->
<nav>

    <!------------ TOP-NAV ------------->
    <div class="top-nav">
        <!-- logo --->
        <div class="logo">
            <a href="/Jewelre/view/main/index.php"><img src="../img/logo-site.png" alt="JE"></a>
            <a href="/Jewelre/view/main/index.php">Jewelr-e</a>
        </div>
        <!-- recherche (voir recherche.php) --->
        <form action="/Jewelre/view/boutique/recherche.php" method="GET">
            <div class="search-bar">
                <input type="search" name="query" placeholder="Chercher un bijou, une collection..." required>
            </div>
        </form>
        <!-- se connecter --->
        <div class="login">
            <?php
            if(isset($_SESSION['admin'])) {
                echo '<a href="#"><i class=\'bx bx-message-alt-minus bx-sm\'></i></a>';
                echo '<a href="#"><i class=\'bx bx-plus bx-sm\'></i></a>';
                echo '<a href="/Jewelre/view/main/profil.php">' . $_SESSION['admin'] . '</a>';
                echo '<a href="/Jewelre/model/logout.php" class="btn"><i class=\'bx bx-log-out bx-sm\'></i></a>';
            } elseif(isset($_SESSION['entreprise'])){
                echo '<a href="/Jewelre/view/vendeur/inventaire.php"><i class=\'bx bx-box bx-sm\'></i></a>';
                echo '<a href="/Jewelre/view/vendeur/add_bague.php"><i class=\'bx bx-plus bx-sm\'></i></a>';
                echo '<a href="/Jewelre/view/main/profil.php">' . $_SESSION['entreprise'] . '</a>';
                echo '<a href="/Jewelre/model/logout.php" class="btn"><i class=\'bx bx-log-out bx-sm\'></i></a>';
            } elseif(isset($_SESSION['pseudo'])){
                echo '<a href="/Jewelre/view/boutique/favoris.php" id="icon">' . ($favoris_exist ? '<i class="bx bxs-heart" id="icon-i"></i>' : '<i class="bx bx-heart bx-sm"></i>') . '<div class="count"><p>' . $nombreFavoris . '</p></div></a>';
                echo '<a href="/Jewelre/view/boutique/panier.php" id="icon">' . ($panier_exist ? '<i class="bx bxs-shopping-bag" id="icon-i"></i>' : '<i class="bx bx-shopping-bag bx-sm"></i>') . '<div class="count"><p>' . $nombreItemsPanierAffichage . '</p></div></a>';
                echo '<a href="/Jewelre/view/main/profil.php">' . $_SESSION['pseudo'] . '</a>';
                echo '<a href="/Jewelre/model/logout.php" class="btn"><i class=\'bx bx-log-out bx-sm\'></i></a>';
            } else {
                echo '<a href="/Jewelre/view/main/login_client.php"><i class=\'bx bx-heart bx-sm\'></i></a>';
                echo '<a href="/Jewelre/view/main/login_client.php"><i class=\'bx bx-shopping-bag bx-sm\'></i></a>';
                echo '<a href="/Jewelre/view/main/login_client.php" class="btn">Se connecter</a>';
            }
            ?>
        </div>
    </div>

    <!------------ BOTTOM-NAV ------------->
    <div class="bottom-nav">
        <ul>
            <?php
            if(isset($_SESSION['admin'])) {
                echo "<li><a href=\"/Jewelre/view/admin/admin_dashboard.php\">DASHBOARD</a></li>";
                echo "<li><a href=\"/Jewelre/view/admin/admin_utilisateurs.php\">UTILISATEURS</a></li>";
                echo "<li><a href=\"/Jewelre/view/admin/admin_produits.php\">PRODUITS</a></li>";
            } else if(isset($_SESSION['entreprise'])) {
                echo "<li><a href=\"/Jewelre/view/vendeur/dashboard.php\">DASHBOARD</a></li>";
                echo "<li><a href=\"/Jewelre/view/vendeur/add_bague.php\">AJOUTER UN ARTICLE</a></li>";
                echo "<li><a href=\"/Jewelre/view/vendeur/inventaire.php\">MES ARTICLES</a></li>";
                echo "<li><a href=\"/Jewelre/view/vendeur/inventaire_collection.php\">MES COLLECTIONS</a></li>";
                echo "<li><a href=\"/Jewelre/view/vendeur/commande.php\">MES COMMANDES</a></li>";
            } else {
                echo "<li><a href=\"/Jewelre/view/boutique/catalogue.php?filtre_bague=on\">BAGUES</a></li>";
                echo "<li><a href=\"/Jewelre/view/boutique/catalogue.php?filtre_earrings=on\">BOUCLES D'OREILLES</a></li>";
                echo "<li><a href=\"/Jewelre/view/boutique/catalogue.php?filtre_bracelet=on\">BRACELETS</a></li>";
                echo "<li><a href=\"/Jewelre/view/boutique/catalogue.php?filtre_collier=on\">COLLIERS</a></li>";
                echo "<li><a href=\"/Jewelre/view/boutique/collection.php\">COLLECTIONS</a></li>";
            }
            ?>
        </ul>
    </div>
    
</nav>