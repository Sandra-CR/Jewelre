<?php
session_start();
if (!isset($_SESSION['entreprise'])) {
    header("Location: /Jewelre/view/main/login_vendeur.php");
    exit();
}

include('../../model/bdd.php');

$idVendeur = $_SESSION['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

$idCollection = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM collection WHERE id = :id");
$stmt->bindParam(':id', $idCollection);
$stmt->execute();
$collection = $stmt->fetch(PDO::FETCH_ASSOC);
$dateFr = DateTime::createFromFormat('Y-m-d', $collection['date_sortie'])->format('d/m/Y');

if ($collection) {
    $stmt = $conn->prepare("SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.id_collection,
       GROUP_CONCAT(CONCAT(ps.matiere, ' ', ps.couleur) ORDER BY ps.id ASC SEPARATOR ' et ') AS pierres_info,
       (SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin
       FROM produit p LEFT JOIN produit_suplement ps ON ps.id_produit = p.id WHERE id_fournisseur = :id_fournisseur AND id_collection IS NULL OR id_collection = :id_collection
       GROUP BY p.id ORDER BY id_collection DESC");
    $stmt->bindParam(':id_fournisseur', $idVendeur);
    $stmt->bindParam(':id_collection', $idCollection);
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['modifier'])) {

    try {
        $titre = $_POST['titre'];
        $date_sortie = $_POST['date_sortie'];
        $produits = isset($_POST['produitItem']) ? $_POST['produitItem'] : [];

        $stmt = $conn->prepare("SELECT id FROM produit WHERE id_collection = :id_collection");
        $stmt->bindParam(':id_collection', $idCollection);
        $stmt->execute();
        $produitsActuels = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $produitsCoches = $produits;
        $produitsDecoches = array_diff($produitsActuels, $produitsCoches);
        $produitsAjoutes = array_diff($produitsCoches, $produitsActuels);

        if (empty($titre) || empty($date_sortie)) {
            $_SESSION['erreur'] = "<span>Certains champs obligatoires sont vides</span>";
            header("Location: collection_page.php?id=" . urlencode($idCollection));
            exit();
        } else {
            if (isset($_POST['envente']) && empty($produits)) {
                $_SESSION['erreur'] = "<span>Veuillez ajouter au minimum un article si vous souhaitez garder
                    en vente cette collection</span>";
                header("Location: collection_page.php?id=" . urlencode($idCollection));
                exit();
            } else {
                if (isset($_POST['envente'])) {
                    $sql = "UPDATE collection SET en_vente = 1, titre = :titre, date_sortie = :date_sortie WHERE id = :id";
                } else {
                    $sql = "UPDATE collection SET en_vente = 0, titre = :titre, date_sortie = :date_sortie WHERE id = :id";
                }
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':titre', $titre);
                $stmt->bindParam(':date_sortie', $date_sortie);
                $stmt->bindParam(':id', $idCollection);
                $stmt->execute();

                foreach ($produitsDecoches as $id) {
                    $stmt = $conn->prepare("UPDATE produit SET id_collection = NULL WHERE id = :id");
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }

                foreach ($produitsAjoutes as $id) {
                    $stmt = $conn->prepare("UPDATE produit SET id_collection = :id_collection WHERE id = :id");
                    $stmt->bindParam(':id_collection', $idCollection);
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                }

                $_SESSION['succes'] = "<span>Collection ajoutée avec succès!</span>";
                header("Location: collection_page.php?id=" . urlencode($idCollection));
                exit();
            }
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/add_article.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <!-- Titre dynamique -->
    <title>
        <?php if (empty($collection)) {
            echo "Collection introuvable | Jewelr-e";
        } else {
            echo "Collection " . $collection['titre'] . " | Jewelr-e";
        } ?>
    </title>
</head>

<body>
    <!------------------------ NAVBAR ------------------------>
    <?php include('../include/navbar.php'); ?>

    <!------------------------ FORMULAIRE ------------------------>
    <div class="page">
        <div class="formulaire">
            <form method="POST">
                <?php
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
                    <div class="caracteristiques plus">
                        <h3>Caractéristiques</h3>

                        <?php if (!empty($collection['image_chemin'])) { ?>
                            <img src="<?php echo $collection['image_chemin']; ?>" alt="Image de la collection <?php echo $collection['titre']; ?>" id="imgCollection">
                        <?php } else { ?>
                            <img src="../img/collection/default.png" alt="Image par défaut" id="imgCollection">
                        <?php } ?>

                        <div class="input-group">
                            <label for="genre">Titre*</label>
                            <input type="text" name="titre" value="<?php echo $collection['titre']; ?>" required>
                        </div>
                        <div class="input-group">
                            <label for="genre">Date de sortie*</label>
                            <input type="date" name="date_sortie" value="<?php echo $collection['date_sortie']; ?>" required>
                        </div>
                        <div class="conditions">
                            <div class="condi">
                                <?php if ($collection['en_vente'] == 1) {
                                    echo '<input type="checkbox" name="envente" checked>';
                                } else {
                                    echo '<input type="checkbox" name="envente">';
                                }
                                ?>
                                <p>Je souhaite mettre en vente cette collection</p>
                            </div>
                        </div>
                        <div class="btn-submit">
                            <input type="submit" name="modifier" value="MODIFIER">
                        </div>
                    </div>
                    <div class="articles-collection plus">
                        <h3>Articles de la collection</h3>
                        <?php if ($produits) { ?>
                            <div class="window">
                                <?php
                                foreach ($produits as $produit) {
                                    $pierresInfo = $produit['pierres_info'];
                                    $stmt = $conn->prepare("SELECT * FROM produit_taille WHERE id_produit = :id_produit LIMIT 1");
                                    $stmt->bindParam(':id_produit', $produit['id']);
                                    $stmt->execute();
                                    $premiereTaille = $stmt->fetch(PDO::FETCH_ASSOC);
                                ?>

                                    <div class="item-group">
                                        <div class="left">
                                            <img src="<?php echo !empty($produit['image_chemin']) ? htmlspecialchars($produit['image_chemin']) : '../img/boutique/default.png'; ?>" alt="">
                                            <label for="produitItem"><a href="../boutique/produit_page.php?id=<?php echo htmlspecialchars($produit['id']); ?>&taille=<?php echo htmlspecialchars($premiereTaille['id']); ?>"><?php echo $produit['type_produit'] . " " . $produit['motif'] . " " . $produit['matiere_p'] . " " . $produit['couleur_p'] . " " . $pierresInfo; ?></a></label>
                                        </div>

                                        <input type="checkbox" name="produitItem[]" value="<?php echo $produit['id']; ?>" <?php if ($produit['id_collection'] == $idCollection) {echo "checked";} ?>>
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
    <?php include('../include/footer.php'); ?>
</body>

</html>