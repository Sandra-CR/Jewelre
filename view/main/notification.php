<?php 
session_start();
if (!isset($_SESSION['pseudo'])) {
    header("Location: login_client.php");
    exit();
}

include ('../../model/bdd.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

$idClient = $_SESSION['id'];
$stmt = $conn->prepare("SELECT * FROM commande_contenu cc LEFT JOIN commande c ON c.id = cc.id_commande WHERE c.id_client = :id_client ORDER BY c.date_commande DESC");
$stmt->bindParam(':id_client', $idClient);
$stmt->execute();
$achats = $stmt->fetchAll(PDO::FETCH_ASSOC);

if(isset($_POST['deposAvis'])) {
    if (!empty($_POST['note']) && !empty($_POST['titre']) && !empty($_POST['description']) && !empty($_POST['id_produit'])) {
        $stmt = $conn->prepare("INSERT INTO commentaire (note, titre, description, date_publication, id_client, id_produit) VALUES (:note, :titre, :description, NOW(), :id_client, :id_produit)");
        $stmt->bindParam(':note', $_POST['note']);
        $stmt->bindParam(':titre', $_POST['titre']);
        $stmt->bindParam(':description', $_POST['description']);
        $stmt->bindParam(':id_client', $idClient);
        $stmt->bindParam(':id_produit', $_POST['id_produit']);
        $stmt->execute();
    } else {
        echo '<p>Veuillez remplir tous les champs</p>';
        exit();
    }
    
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/notification.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes notifications | Jewelr-e</title>
</head>
<body>
    <?php include ('../include/navbar.php'); ?>

    <div class="notif-box">
        <div class="notif-header">
            <h2>Mes notifications</h2>  
        </div>
        <div class="notif-box-content">
            <?php
            if($achats) {
                foreach($achats as $achat) {
                    $stmt = $conn->prepare("SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.matiere_s, p.couleur_s,c.titre,
                    GROUP_CONCAT(CONCAT(ps.matiere, ' ', ps.couleur) ORDER BY ps.id ASC SEPARATOR ' et ') AS pierres_info
                    FROM produit p
                    LEFT JOIN produit_suplement ps ON ps.id_produit = p.id
                    LEFT JOIN collection c ON c.id = p.id_collection
                    WHERE p.id = :id_produit");
                    $stmt->bindParam(':id_produit', $achat['id_produit']);
                    $stmt->execute();
                    $achatDesc = $stmt->fetch(PDO::FETCH_ASSOC);
                    $pierresInfo = $achatDesc['pierres_info'];

                    $stmt = $conn->prepare("SELECT * FROM commentaire WHERE id_client = :id_client AND id_produit = :id_produit");
                    $stmt->bindParam(':id_client', $idClient);
                    $stmt->bindParam(':id_produit', $achat['id_produit']);
                    $stmt->execute();
                    $isCommented = $stmt->fetch(PDO::FETCH_ASSOC);

                    if($isCommented) { ?>
                        <div class="notif-item done">
                            <div class="left">
                                <i class='bx bxs-message-check'></i>
                                <p>Vous avez commandé: <?php echo $achatDesc['type_produit']." ".$achatDesc['motif']." ".$achatDesc['matiere_p']." ".$achatDesc['couleur_p']." ".$achatDesc['matiere_s']." ".$achatDesc['couleur_s']." ".$pierresInfo; ?></p>
                            </div>
                            <div class="right">
                                <p>Avis déposé</p>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="notif-item">
                            <div class="left">
                                <i class='bx bxs-message-alt-edit'></i>
                                <p>Vous avez commandé: <?php echo $achatDesc['type_produit']." ".$achatDesc['motif']." ".$achatDesc['matiere_p']." ".$achatDesc['couleur_p']." ".$achatDesc['matiere_s']." ".$achatDesc['couleur_s']." ".$pierresInfo; ?></p>
                            </div>
                            <div class="right">
                                <button id="deposAvisPopup_<?php echo $achat['id_produit']; ?>" class="depos-avis" type="button">Déposer un avis</button>
                            </div>
                        </div>
                    <?php } ?>
                    <div id="popupForm_<?php echo $achat['id_produit']; ?>" class="popup">
                        <div class="popup-content">
                            <span id="closePopup" class="close">&times;</span>
                            <h2>Ajouter un commentaire</h2>
                            <form method="post">
                                <input type="hidden" name="note" id="note" value="0">
                                <input type="hidden" name="id_produit" value="<?php echo $achat['id_produit']; ?>">
                                <div class="etoiles">
                                    <?php for($i = 1; $i <= 5; $i++) { ?>
                                    <span class="etoile" data_value="<?= $i; ?>"><i class='bx bxs-star'></i></span>
                                    <?php } ?>
                                </div>
                                <div class="input-group-popup">
                                    <label for="pop_titre">Titre</label>
                                    <input type="text" name="titre">
                                </div>
                                <div class="input-group-popup">
                                    <label for="pop_description">Description</label>
                                    <textarea name="description"></textarea>
                                </div>
                                <button type="submit" name="deposAvis">Ajouter</button>
                            </form>
                        </div>
                    </div>
                <?php }
            } 
        ?>
        </div>
    </div>

    <?php include ('../include/footer.php'); ?>
</body>
<script>
    const etoiles = document.querySelectorAll(".etoile");
    const noteInput = document.getElementById("note");

    etoiles.forEach(etoile => {
        etoile.addEventListener("click", function () {
            let value = this.getAttribute("data_value");
            noteInput.value = value;

            etoiles.forEach(s => s.classList.remove("selected"));
            for(i = 0; i < value; i++) {
                etoiles[i].classList.add("selected");
            }
        })
    })



    document.querySelectorAll('.depos-avis').forEach(button => {
        button.addEventListener("click", (event) => {
            event.preventDefault();
            const productId = event.target.id.split('_')[1];
            const popup = document.getElementById(`popupForm_${productId}`);
            popup.style.display = "flex"; 
        });
    });

    document.querySelectorAll('.close').forEach(button => {
        button.addEventListener("click", (event) => {
            const productId = event.target.id.split('_')[1];
            const popup = document.getElementById(`popupForm_${productId}`);
            popup.style.display = "none";
        });
    });

    window.addEventListener("click", (event) => {
        if (event.target.classList.contains('popup')) {
            event.target.style.display = "none";
        }
    });

</script>
</html>