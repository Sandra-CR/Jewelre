<?php 
session_start(); 
if (!isset($_SESSION['pseudo'])) {
    header("Location: login_client.php");
    exit();
}

include ('../../model/bdd.php');

$idClient = $_SESSION['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

// Récupérer les éléments du panier
$panier_items = isset($_SESSION['panier']) && !empty($_SESSION['panier']) ? $_SESSION['panier'] : [];
$total = 0;

// Vider le panier si le formulaire est soumis
if (isset($_POST['viderPanier'])) {
    unset($_SESSION['panier']);
    header('Location: panier.php');
    exit();
}

if(isset($_POST['supprimer-item'])) {
    $item = $_POST['item'];
    if (isset($_SESSION['panier']) && is_array($_SESSION['panier'])) {
        foreach ($_SESSION['panier'] as $key => $value) {
            if ($value === $item) {
                unset($_SESSION['panier'][$key]);
                $_SESSION['panier'] = array_values($_SESSION['panier']);
                break;
            }
        }
    }
    header('Location: panier.php');
    exit();
}

if(isset($_POST['finaliserPanier'])) {
    $stmt = $conn->prepare("SELECT * FROM adresse WHERE id_client = :id_client AND active = 1");
    $stmt->bindParam(':id_client', $idClient);
    $stmt->execute();
    $adresse = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($adresse) {
        $totalCommande = 0;
        foreach ($_SESSION['panier'] as $item) {
            list($id_produit, $id_taille) = explode("-", $item);

            $stmt = $conn->prepare("SELECT prix FROM produit p JOIN produit_taille pt ON pt.id_produit = p.id WHERE p.id = :id_produit AND pt.id = :id_taille");
            $stmt->bindParam(':id_produit', $id_produit);
            $stmt->bindParam(':id_taille', $id_taille);
            $stmt->execute();
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($produit) {
                $totalCommande += $produit['prix']; 
            }
        }
 
        $stmt = $conn->prepare("INSERT INTO commande (date_commande, prix_total, id_adresse, id_client) VALUES (NOW(), :prix_total, :id_adresse, :id_client)");
        $stmt->bindParam(':prix_total', $totalCommande);
        $stmt->bindParam(':id_adresse', $adresse['id']);
        $stmt->bindParam(':id_client', $idClient);
        $stmt->execute();

        $id_commande = $conn->lastInsertId();

        foreach($_SESSION['panier'] as $item) {
            list($id_produit, $id_taille) = explode("-", $item);
            $sql = "SELECT p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.matiere_s, p.couleur_s, 
              GROUP_CONCAT(CONCAT(ps.matiere, ' ', ps.couleur) ORDER BY ps.id ASC SEPARATOR ' et ') AS pierres_info  
              FROM produit p LEFT JOIN produit_suplement ps ON ps.id_produit = p.id WHERE p.id = :id_produit GROUP BY p.id;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_produit', $id_produit);
            $stmt->execute();
            $titre = $stmt->fetch(PDO::FETCH_ASSOC);
            $pierresInfo = $titre['pierres_info'];

            $nomProduit = $titre['type_produit'] ." ". (!empty($titre['motif']) ? $titre['motif'] ." " : '') . $titre['matiere_p'] ." ". $titre['couleur_p'] . (!empty($pierresInfo) ? " ". $pierresInfo : '');
            $stmt = $conn->prepare("INSERT INTO commande_contenu (id_commande, id_produit, nom_produit, prix_achat) VALUES (:id_commande, :id_produit, :nom_produit, :prix_achat)");
            $stmt->bindParam(':id_commande', $id_commande);
            $stmt->bindParam(':id_produit', $id_produit);
            $stmt->bindParam(':nom_produit', $nomProduit);
            $stmt->bindParam(':prix_achat', $produit['prix']);
            $stmt->execute();
        }



        unset($_SESSION['panier']);
        $_SESSION['commandePassee'] = true;
        header('Location: panier.php');
        exit();
    } else {
        $_SESSION['message'] = 'Veuillez enregistrer une adresse de livraison à votre compte pour procéder';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/panier.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon panier | Jewelr-e</title>
</head>
<body>
    <?php include ('../include/navbar.php'); ?>

    <?php if (!empty($panier_items)): ?>
        <div class="panier">
            <div class="panier-contenu">
                <?php
                if (isset($_SESSION['message'])) {
                    echo '<p id="message"><i class=\'bx bxs-error-circle bx-sm\'></i>' . $_SESSION['message'] . '</p>';
                    unset($_SESSION['message']);
                }
                ?>
                <table>
                    <thead>
                        <tr>
                            <th id="imageCol">Image</th>
                            <th>Description</th>
                            <th>Prix</th>
                            <th>Collection</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($panier_items as $item): ?>
                                <?php 
                                list($id_produit, $id_taille) = explode("-", $item);
                                $sql = "SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.prix, p.matiere_s, p.couleur_s, p.id_collection, 
                                c.titre, g.genre, pt.id AS taille_id, pt.tour_doigt, pt.longueur, pt.largeur, pt.quantite, 
                                GROUP_CONCAT(
                                CONCAT(ps.matiere, ' ', ps.couleur) ORDER BY ps.id ASC SEPARATOR ' et ') AS pierres_info, 
                                (SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin 
                                FROM produit p JOIN produit_taille pt ON pt.id_produit = p.id 
                                LEFT JOIN produit_suplement ps ON ps.id_produit = p.id 
                                LEFT JOIN collection c ON c.id = p.id_collection 
                                LEFT JOIN genre g ON g.id = p.id_genre 
                                WHERE p.en_vente = 1 AND p.id = :id_produit AND pt.id = :id_taille 
                                GROUP BY p.id, pt.id, c.titre, g.genre";


                                $stmt = $conn->prepare($sql);
                                $stmt->bindParam(':id_produit', $id_produit);
                                $stmt->bindParam(':id_taille', $id_taille);
                                $stmt->execute();
                                $produit = $stmt->fetch(PDO::FETCH_ASSOC);

                                if ($produit) {
                                    $qt = isset($_POST['qt'][$id_produit ."-". $id_taille]) ? $_POST['qt'][$id_produit ."-". $id_taille] : 1;
                                    $total += $produit['prix'] * $qt; ?>
                                    <tr>
                                        <td id="image">
                                            <img src="<?php echo !empty($produit['image_chemin']) ? htmlspecialchars($produit['image_chemin']) : '../img/boutique/default.png'; ?>" alt="<?php echo htmlspecialchars($produit['type_produit']); ?>">
                                        </td>
                                        <td id="titre">
                                            <?php $pierresInfo = $produit['pierres_info']; ?>
                                            <p id="type"><?php echo htmlspecialchars($produit['type_produit']) ." ". $produit['motif'] ." ". $produit['matiere_p'] ." ". $produit['couleur_p']; ?></p>
                                            <p><?php echo $pierresInfo; ?></p>
                                            <br>
                                            <?php if($produit['type_produit'] == 'Bague') { ?>
                                                <p><?php echo 'Taille: ' . $produit['tour_doigt']; ?></p>
                                            <?php } else { ?>
                                                <p><?php echo 'Taille: ' . $produit['longueur'] ."L x". $produit['largeur']."l"; ?></p>
                                            <?php } ?>
                                            
                                        </td>
                                        <td><p><?php echo htmlspecialchars($produit['prix'])."€"?></p></td>
                                        <td>
                                            <?php
                                            if($produit['id_collection'] != NULL) {
                                                $stmt = $conn->prepare("SELECT * FROM `collection` WHERE id = :id");
                                                $stmt->bindParam(':id', $produit['id_collection']);
                                                $stmt->execute();
                                                $collection = $stmt->fetch(PDO::FETCH_ASSOC);           //
                                                ?><p><?php echo $collection['titre'];?></p><?php        // AJOUTER UN LIEN VERS COLLECTION
                                                ?><p><?php echo $collection['date_sortie'];?></p><?php  //
                                            } else { ?>
                                                <p>Hors collection</p>
                                            <?php }
                                            ?>
                                        </td>
                                        <td>
                                            <form method="POST">
                                                <input type="hidden" name="item" value="<?php echo $item; ?>">
                                                <button type="submit" name="supprimer-item" id="btn-suppr"><i class='bx bxs-trash-alt  bx-sm'></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php }
                            endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="menu-droite">
                <div class="prix-case">
                    <p>Total</p> 
                    <p id="prix-total"><?php echo htmlspecialchars($total);?>€</p>
                </div>

                <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir vider le panier ?');">
                    <button type="submit" name="viderPanier">Vider le panier</button>
                </form>
                <form method="POST" id="panierForm">
                    <button type="submit" name="finaliserPanier" id="finaliserBtn">Finaliser le panier</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="panier-vide">
            <div class="bulle">
                <i class='bx bx-shopping-bag bx-lg'></i>
                <?php if(isset($_SESSION['commandePassee'])) {
                    echo '<p>Votre commande est passée !</p>';
                    unset($_SESSION['commandePassee']);
                } else {
                    echo '<p>Votre panier est vide</p>';
                } ?>
                <a href="../main/index.php">Accueil</a>
            </div>
        </div>
    <?php endif; ?>
    <div id="loadingScreen">
        <div>
            <i class='bx bx-loader-alt bx-spin' id="loadingIcon"></i>
            <p>Chargement en cours...</p>
            <p>Veuillez patienter</p>
        </div>
    </div>

    <?php include ('../include/footer.php'); ?>

    <script>
        document.getElementById('finaliserBtn').addEventListener('click', function(event) {
            document.getElementById('loadingScreen').style.display = 'flex';

            event.stopImmediatePropagation();
            setTimeout(function() {
                document.getElementById('panierForm').submit();
            }, 5000);
        });
    </script>
</body>
</html>