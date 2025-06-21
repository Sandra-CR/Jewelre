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

if(isset($_POST['ajouterAuPanier'])) {
    $id_produit = $_GET['id'];
    $id_taille = $_GET['taille'];
    if(!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }
    $_SESSION['panier'][] = $id_produit."-".$id_taille;
    header("Location: panier.php");
}

if(isset($_SESSION['pseudo'])){
    $id_produit = (int) $_GET['id'];
    $id_client = (int) $_SESSION['id'];

    if(isset($_POST['ajouterAuxFavoris'])) {
        $_SESSION['temp_id_produit'] = $_GET['id'];
    }
}
$idProduit = $_GET['id'];
$idTailleActive = $_GET['taille'];
$stmt = $conn->prepare("SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.matiere_s, p.couleur_s, p.prix, p.id_fournisseur,
(SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin,
ps.matiere, ps.couleur, ps.forme, c.titre, g.genre
FROM produit p 
LEFT JOIN collection c ON c.id = p.id_collection
LEFT JOIN produit_suplement ps ON ps.id_produit = p.id
LEFT JOIN genre g ON g.id = p.id_genre
WHERE p.en_vente = 1 AND p.id = :id;");
$stmt->bindParam(':id', $idProduit, PDO::PARAM_INT);
$stmt->execute();
$produit = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/produit_page.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php if(empty($produit)) {
            echo "Article introuvable | Jewelr-e";
        } else {
            echo $produit['type_produit'] ." ". $produit['motif'] ." ". $produit['matiere_p'] ." ". $produit['couleur_p'] ." ". $produit['matiere_s'] ." ". $produit['couleur_s'] ." ". $produit['forme'] ." ". $produit['matiere'] ." ". $produit['couleur'] ." | Jewelr-e";
        } ?>
    </title>
</head>
<body>
    <?php
    include ('../include/navbar.php');
    if ($produit) { 
        $stmt = $conn->prepare("SELECT image_chemin FROM produit_image WHERE id_produit = :id_produit");
        $stmt->bindParam(':id_produit', $idProduit);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <div class="retour">
        <button onclick="history.back();" id="btnRetour">< Retour</button>
    </div>
    <div class="produit">
        <div class="images-produit">
            <div class="slider">
                <div class="slides">
                <?php foreach ($images as $image): ?>
                    <img class="slide" src="<?php echo $image['image_chemin']; ?>" alt="#">
                <?php endforeach; 
                ?>
                </div>
                <button class="prev" onclick="prevSlide()"><i class='bx bx-chevron-left bx-lg'></i></button>
                <button class="next" onclick="nextSlide()"><i class='bx bx-chevron-right bx-lg'></i></button>
            </div>
        </div>

        <div class="infos-produit">
            <div class="haut-desc">
                <h1><?php echo htmlspecialchars($produit['type_produit']) ." ". htmlspecialchars($produit['motif']) ." ". htmlspecialchars($produit['matiere_p']) ." ". htmlspecialchars($produit['couleur_p'] ." ". $produit['matiere_s'] ." ". $produit['couleur_s'] ." ". $produit['forme'] ." ". $produit['matiere'] ." ". $produit['couleur']) ?></h1>
                <h3><?php echo htmlspecialchars($produit['prix']) . " €";?></h3>
            </div>

            <div class="avis">
                <?php
                $stmt = $conn->prepare("SELECT * FROM commentaire WHERE id_produit = :id_produit");
                $stmt->bindParam(':id_produit', $idProduit);
                $stmt->execute();
                $avis = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $totalNote = 0;
                
                if($avis) {
                    foreach($avis as $item) { $totalNote += $item['note']; }
                    $moyenneNote = $totalNote / count($avis);
                    switch ($moyenneNote) {
                        case ($moyenneNote == 5): echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i>"; break;
                        case ($moyenneNote >= 4.5): echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star-half'></i>"; break;
                        case ($moyenneNote >= 4): echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bx-star'></i>"; break;
                        case ($moyenneNote >= 3.5): echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star-half'></i><i class='bx bx-star'></i>"; break;
                        case ($moyenneNote >= 3): echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i>"; break;
                        case ($moyenneNote >= 2.5): echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star-half'></i><i class='bx bx-star'></i><i class='bx bx-star'></i>"; break;
                        case ($moyenneNote >= 2): echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i>"; break;
                        case ($moyenneNote >= 1.5): echo "<i class='bx bxs-star'></i><i class='bx bxs-star-half'></i><i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i>"; break;
                        case ($moyenneNote = 1): echo "<i class='bx bxs-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i>"; break;
                        default: echo "<i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i>"; break;
                    }
                }

                ?>
                <p><?php echo !empty($avis) ? round($moyenneNote, 1) : '<i class=\'bx bx-star\'></i><i class=\'bx bx-star\'></i><i class=\'bx bx-star\'></i><i class=\'bx bx-star\'></i><i class=\'bx bx-star\'></i>'; ?></p>
                <a href="#avis">(<?php echo !empty($avis) ? count($avis) . ' avis' : 'Aucun avis'; ?>)</a>
            </div>

            <div class="vendeur">
                <?php 
                $stmt = $conn->prepare("SELECT entreprise FROM fournisseur WHERE id = :id");
                $stmt->bindParam(':id', $produit['id_fournisseur']);
                $stmt->execute();
                $vendeur = $stmt->fetch();
                ?>
                <h4>Produit fabriqué et vendu par <span><?php echo htmlspecialchars($vendeur['entreprise']); ?></h4>
            </div>

            <div class="tailles">
                <div class="taille-grid">
                    <?php 
                    $stmt = $conn->prepare("SELECT * FROM produit_taille WHERE id_produit = :id_produit");
                    $stmt->bindParam(':id_produit', $idProduit);
                    $stmt->execute();
                    $tailles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($tailles) === 1) {
                        $tailleUnique = $tailles[0]; 
                        ?>
                        <a href="#" id="taille active" class="taille active">Taille unique</a>
                        <?php
                    } else {
                        foreach ($tailles as $taille): ?>
                            <?php if($taille['id'] == $idTailleActive): ?>
                                <a href="#" class="taille active">
                                    <?php echo !empty($taille['tour_doigt']) ? $taille['tour_doigt'] : $taille['longueur'] . " L x " . $taille['largeur'] . " l"; ?>
                                </a>
                            <?php else: ?>
                                <a href="produit_page.php?id=<?php echo htmlspecialchars($idProduit); ?>&taille=<?php echo htmlspecialchars($taille['id']); ?>" class="taille">
                                    <?php echo !empty($taille['tour_doigt']) ? $taille['tour_doigt'] : $taille['longueur'] . " L x " . $taille['largeur'] . " l"; ?>
                                </a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php } ?>
                </div>
                
                <?php
                $stmt = $conn->prepare("SELECT * FROM produit_taille WHERE id = :id");
                $stmt->bindParam(':id', $idTailleActive);
                $stmt->execute();
                $tailleActive = $stmt->fetch();
                ?><div class="stock-text"><?php
                    if($tailleActive['quantite'] >= 10) { ?>
                        <p><span id="stock">En stock !</span> commandez dès maintenant</p>
                    <?php } elseif($tailleActive['quantite'] > 0) { ?>
                        <p><span id="stockLim">ATTENTION ! </span>Il ne reste que <?php echo $tailleActive['quantite']; ?> exemplaires</p>
                    <?php } else { ?>
                        <p><span id="ruptureStock">Hors stock...</span> Prochainement disponible</p>
                    <?php } ?>
                </div>
                <div class="panier-ajout">
                    <?php if($tailleActive['quantite'] == 0) { ?>
                        <button class="btn-panier">Indisponible</button>
                    <?php } else { ?>
                        <form method="POST" id="achat-btns">
                            <input type="hidden" name="idProduit" value="<?php echo htmlspecialchars($idProduit) ;?>">
                            <button type="submit" name="ajouterAuPanier" class="btn-panier on">Ajouter au panier</button>
                            <button type="submit" name="ajouterAuxFavoris" id="ajouterAuxFavoris" 
                              data-produit-id="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>">
                              <i class='bx bxs-heart bx-sm' id="favorisIcon"></i>
                            </button>

                        </form>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="produit-desc vente">
        <div class="desc-group">
            <h2>Détail produit</h2>
            <div class="donnee-group"><h5>Genre</h5> <p><?php echo $produit['genre']; ?></p></div>
            <?php if(!empty($tailleActive['tour_doigt'])) { ?>
                <div class="donnee-group"><h5>Tour de doigt</h5> <p><?php echo $tailleActive['tour_doigt']; ?></p></div>
            <?php } ?>
            <div class="donnee-group"><h5>Poids total (gr)</h5> <p><?php echo $tailleActive['poids']; ?></p></div>
            <div class="donnee-group"><h5>Matière principale</h5> <p><?php echo $produit['matiere_p']; ?></p></div>
            <div class="donnee-group"><h5>Couleur principale</h5> <p><?php echo $produit['couleur_p']; ?></p></div>
            <?php if(!empty($produit['matiere_s'])) { ?>
                <div class="donnee-group"><h5>Matière secondaire</h5> <p><?php echo $produit['matiere_s']; ?></p></div>
                <div class="donnee-group"><h5>Couleur secondaire</h5> <p><?php echo $produit['couleur_s']; ?></p></div>
            <?php } 
            if(!empty($produit['titrage'])) { ?>
                <div class="donnee-group"><h5>Titrage</h5> <p><?php echo $produit['titrage']; ?>/1000</p></div>
            <?php }
            if(!empty($produit['motif'])) { ?>
                <div class="donnee-group"><h5>Forme</h5> <p><?php echo $produit['motif']; ?></p></div>
            <?php }
            if(!empty($produit['chaine'])) { ?>
                <div class="donnee-group"><h5>Type de chaine</h5> <p><?php echo $produit['chaine']; ?></p></div>
            <?php } 
            if(!empty($tailleActive['longueur']) && !empty($tailleActive['largeur'])) { ?>
                <div class="donnee-group"><h5>Dimension</h5> <p><?php echo $tailleActive['longueur'] ."L x ". $tailleActive['largeur'] ."l"; ?></p></div>
            <?php } 
            if(!empty($produit['fermoir'])) {?>
                <div class="donnee-group"><h5>Type de fermoir</h5> <p><?php echo $produit['fermoir']; ?></p></div>
            <?php } ?>
        </div>
        <?php 
        $stmt = $conn->prepare("SELECT * FROM produit_suplement WHERE id_produit = :id_produit");
        $stmt->bindParam(':id_produit', $idProduit);
        $stmt->execute();
        $pierres = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($pierres as $pierre) { ?>
            <div class="desc-group">
                <h2><?php echo $pierre['type_sup']; ?></h2>
                <div class="donnee-group"><h5>Type de pierre</h5> <p><?php echo $pierre['matiere']; ?></p></div>
                <div class="donnee-group"><h5>Couleur</h5> <p><?php echo $pierre['couleur']; ?></p></div>
                <?php if(!empty($pierre['nombre'])) { ?>
                    <div class="donnee-group"><h5>Nombres de pierres</h5> <p><?php echo $pierre['nombre']; ?></p></div>
                <?php } ?>
                <?php if(!empty($pierre['forme'])) { ?>
                    <div class="donnee-group"><h5>Forme du pendentif</h5> <p><?php echo $pierre['forme']; ?></p></div>
                <?php } ?>
                <?php if(!empty($pierre['caratage'])) { ?>
                    <div class="donnee-group"><h5>Caratage</h5> <p><?php echo $pierre['caratage']; ?></p></div>
                <?php } ?>
                <?php if(!empty($pierre['sertis'])) { ?>
                    <div class="donnee-group"><h5>Type de sertis</h5> <p><?php echo $pierre['sertis']; ?></p></div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <div class="avis-container" id="avis">
        <?php 
        $stmt = $conn->prepare("SELECT cm.*, c.pseudo FROM commentaire cm LEFT JOIN client c ON c.id = cm.id_client WHERE cm.id_produit = :id_produit ORDER BY cm.date_publication DESC");
        $stmt->bindParam(':id_produit', $idProduit);
        $stmt->execute();
        $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <h2>Commentaires vérifiés (<?php echo count($commentaires); ?>)</h2>
        <div class="grid">
            <?php 
            if(isset($_SESSION['pseudo'])) {
                $idClient = $_SESSION['id'];
                $stmt = $conn->prepare("SELECT * FROM commentaire WHERE id_client = :id_client");
                $stmt->bindParam(':id_client', $idClient);
                $stmt->execute();
                $aCommentaire = $stmt->fetchAll(PDO::FETCH_ASSOC);

                $stmt = $conn->prepare("SELECT * FROM commande_contenu cc LEFT JOIN commande c ON c.id = cc.id_commande WHERE c.id_client = :id_client AND cc.id_produit = :id_produit");
                $stmt->bindParam(':id_client', $idClient);
                $stmt->bindParam(':id_produit', $idProduit);
                $stmt->execute();
                $aCommande = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if(count($aCommentaire) == 0 && count($aCommande) != 0) { ?>
                    <div class="avis-item add">
                        <form method="POST">
                            <input type="text" name="titre">
                            <textarea name="description"></textarea>
                            <button tyepe="submit">Publier mon avis</button>
                        </form>
                    </div>                    


                <?php } 
            }
            foreach($commentaires as $commentaire) { 
                list($dateEn, $heureFull) = explode(" ", $commentaire['date_publication']);
                $dateFr = DateTime::createFromFormat('Y-m-d', $dateEn)->format('d/m/Y');
                $heureFormat = date("H:i", strtotime($heureFull));
                ?>
                <div class="avis-item">
                    <div class="avis-info">
                        <div class="left">
                            <img src="<?php echo !empty($commentaire['pdp']) && file_exists('../img/profile/' . $vendeur['pdp']) ? '../img/profile/' . $commentaire['pdp'] : '../img/profile/default1.png'; ?>" alt="Photo de profil de <?php echo $commentaire['pseudo']; ?>">
                            <h4><?php echo $commentaire['pseudo']; ?></h4>
                        </div>
                        <div class="right">
                            <p id="datePost">Publié le <?php echo $dateFr ." à ". $heureFormat ?></p>
                        </div>
                    </div>
                    <div class="avis-content">
                        <div class="titre">
                            <div class="etoiles">
                                <?php  
                                switch ($commentaire['note']) {
                                    case 1:
                                        echo "<i class='bx bxs-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i>";
                                        break;
                                    case 2:
                                        echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i>";
                                        break;
                                    case 3:
                                        echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bx-star'></i><i class='bx bx-star'></i>";
                                        break;
                                    case 4:
                                        echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bx-star'></i>";
                                        break;
                                    case 5:
                                        echo "<i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i><i class='bx bxs-star'></i>";
                                        break;
                                }
                                ?>
                            </div>
                            <h5><?php echo $commentaire['titre']; ?></h3>
                        </div>
                        <p><?php echo $commentaire['description']; ?></p>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <?php 
    } else { ?>
        <div class="article-introuvable">
            <div class="bulle">
                <i class='bx bxs-info-circle bx-lg'></i>
                <p>Article introuvable</p>
                <a href="../main/index.php">Accueil</a>
            </div>
        </div>
    <?php }
    include ('../include/footer.php');
    ?>

    <!------------------------ LIAISONS JS ------------------------>
    <script src="../js/slider_article.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
        const favorisIcon = document.getElementById('favorisIcon');
        const ajouterAuxFavorisButton = document.getElementById('ajouterAuxFavoris');
        const idProduit = ajouterAuxFavorisButton.getAttribute('data-produit-id');  // Récupère l'ID depuis l'attribut

        if (favorisIcon) {
            // Fonction pour vérifier l'état des favoris
            function checkFavorisStatus() {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "/Jewelre/model/check_favoris.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        console.log("Réponse du serveur pour check_favoris.php: ", xhr.responseText);
                        // Vérifier la réponse du serveur (1 ou 0)
                        if (xhr.responseText === "1") {
                            // Produit dans les favoris, mettre à jour l'icône
                            favorisIcon.classList.remove('bx-heart');
                            favorisIcon.classList.add('bxs-heart');
                        } else {
                            // Produit pas dans les favoris, mettre à jour l'icône
                            favorisIcon.classList.remove('bxs-heart');
                            favorisIcon.classList.add('bx-heart');
                        }
                    } else {
                        console.error("Erreur lors de la requête check_favoris.php: ", xhr.status);
                    }
                };

                // Envoi de l'ID du produit via POST
                xhr.send("idProduit=" + encodeURIComponent(idProduit));
            }

            // Vérifier l'état des favoris au chargement de la page
            checkFavorisStatus();

            // Ajouter ou retirer des favoris lorsqu'on clique sur le bouton
            ajouterAuxFavorisButton.addEventListener('click', function (event) {
                event.preventDefault();

                var xhr = new XMLHttpRequest();
                xhr.open("POST", "/Jewelre/model/toggle_favoris.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                xhr.onload = function () {
                    if (xhr.status === 200) {
                        console.log("Réponse du serveur pour toggle_favoris.php: ", xhr.responseText);
                        // Mettre à jour l'état des favoris après ajout ou suppression
                        checkFavorisStatus();
                    } else {
                        console.error("Erreur lors de la requête toggle_favoris.php: ", xhr.status);
                    }
                };

                // Envoi de l'ID du produit via POST
                xhr.send("idProduit=" + encodeURIComponent(idProduit));
            });
        } else {
            console.error('L\'élément avec l\'ID "favorisIcon" n\'a pas été trouvé.');
        }
    });

    </script>

</body>
</html>

