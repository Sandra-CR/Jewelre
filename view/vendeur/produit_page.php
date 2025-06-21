<?php 
session_start(); 
if (!isset($_SESSION['entreprise'])) {
    header("Location: /Jewelre/view/main/login_vendeur.php");
    exit();
}

// Connexion
include ('../../model/bdd.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

// Si le bouton supprimer est validé
if(isset($_POST['supprimerArticle'])) {
        $id_produit = $_GET['id']; 
    
        // Sécurisation du traitement
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("DELETE FROM produit_taille WHERE id_produit = :id");
        $stmt->bindParam(':id', $id_produit, PDO::PARAM_INT);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM produit_suplement WHERE id_produit = :id");
        $stmt->bindParam(':id', $id_produit, PDO::PARAM_INT);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM produit_image WHERE id_produit = :id");
        $stmt->bindParam(':id', $id_produit, PDO::PARAM_INT);
        $stmt->execute();
        
        $stmt = $conn->prepare("DELETE FROM produit WHERE id = :id");
        $stmt->bindParam(':id', $id_produit, PDO::PARAM_INT);
        $stmt->execute();
        
        // Commit de la transaction
        $conn->commit();
}

// Ajout aux favoris
if(isset($_SESSION['pseudo'])){
    $id_produit = (int) $_GET['id'];
    $id_client = (int) $_SESSION['id'];

    if(isset($_POST['ajouterAuxFavoris'])) {
        $_SESSION['temp_id_produit'] = $_GET['id'];
    }
}

// Appel des données produit
$idProduit = $_GET['id'];
$idTailleActive = $_GET['taille'];
$stmt = $conn->prepare("SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.matiere_s, p.couleur_s, p.prix, p.id_genre, p.id_fournisseur,
(SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin,
ps.matiere, ps.couleur, ps.forme, c.titre, g.genre
FROM produit p 
LEFT JOIN collection c ON c.id = p.id_collection
LEFT JOIN produit_suplement ps ON ps.id_produit = p.id
LEFT JOIN genre g ON g.id = p.id_genre
WHERE p.id = :id;");
$stmt->bindParam(':id', $idProduit, PDO::PARAM_INT);
$stmt->execute();
$produit = $stmt->fetch();
?>

<?php // Popup qui permet d'ajouter une taille à l'article
if(isset($_POST['ajouterTaille'])) {
    if($produit['type_produit'] == 'Bague') {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM produit_taille WHERE tour_doigt = :tour_doigt AND id_produit = :id_produit");
        $stmt->bindParam(':tour_doigt', $_POST['pop_tour_doigt']);
        $stmt->bindParam(':id_produit', $idProduit);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['erreurTaille'] = 'Cette taille existe déjà pour ce produit';
            header('Location: produit_page.php?id=' . $idProduit .'&taille=' . $_GET['taille']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO produit_taille (tour_doigt, poids, quantite, id_produit) VALUES (:tour_doigt, :poids, :quantite, :id_produit)");
        $stmt->bindParam(':tour_doigt', $_POST['pop_tour_doigt']);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM produit_taille WHERE longueur = :longueur AND largeur = :largeur AND id_produit = :id_produit");
        $stmt->bindParam(':longueur', $_POST['pop_longueur']);
        $stmt->bindParam(':largeur', $_POST['pop_largeur']);
        $stmt->bindParam(':id_produit', $idProduit);
        $stmt->execute();
        $count = $stmt->fetchColumn();

        if ($count > 0) {
            $_SESSION['erreurTaille'] = 'Cette taille existe déjà pour ce produit';
            header('Location: produit_page.php?id=' . $idProduit .'&taille=' . $_GET['taille']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO produit_taille (longueur, largeur, poids, quantite, id_produit) VALUES (:longueur, :largeur, :poids, :quantite, :id_produit)");
        $stmt->bindParam(':longueur', $_POST['pop_longueur']);
        $stmt->bindParam(':largeur', $_POST['pop_largeur']);
    }
    $stmt->bindParam(':poids', $_POST['pop_poids']);
    $stmt->bindParam(':quantite', $_POST['pop_quantite']);
    $stmt->bindParam(':id_produit', $idProduit);
    $stmt->execute();
} 

// Modifier les données de l'article
if(isset($_POST['modifierArticle'])) {
    // Vérifie les champs obligatoires
    if (!empty($_POST['prix']) && !empty($_POST['matiere_p']) && !empty($_POST['couleur_p']) && !empty($_POST['genre'])) {
        $modifProduitTaille = 0;
        if ($produit['type_produit'] === "Bague") {
            if (!empty($_POST['quantite']) && !empty($_POST['poids']) && !empty($_POST['tour_doigt'])) {
                $stmt = $conn->prepare("UPDATE produit_taille SET quantite = :quantite, poids = :poids, tour_doigt = :tour_doigt WHERE id = :id AND id_produit = :id_produit");
                $stmt->bindParam(':quantite', $_POST['quantite']);
                $stmt->bindParam(':poids', $_POST['poids']);
                $stmt->bindParam(':tour_doigt', $_POST['tour_doigt']);
                $stmt->bindParam(':id', $idTailleActive);
                $stmt->bindParam(':id_produit', $idProduit);
                $stmt->execute();
                $modifProduitTaille = $stmt->rowCount();
            } else {
                $_SESSION['erreur'] = "Veuillez remplir tous les champs obligatoires.";
            }
        } else {
            if (!empty($_POST['quantite']) && !empty($_POST['poids']) && !empty($_POST['longueur']) && !empty($_POST['largeur'])) {
                $stmt = $conn->prepare("UPDATE produit_taille SET quantite = :quantite, poids = :poids, longueur = :longueur, largeur = :largeur WHERE id = :id AND id_produit = :id_produit");
                $stmt->bindParam(':quantite', $_POST['quantite']);
                $stmt->bindParam(':poids', $_POST['poids']);
                $stmt->bindParam(':longueur', $_POST['longueur']);
                $stmt->bindParam(':largeur', $_POST['largeur']);
                $stmt->bindParam(':id', $idTailleActive);
                $stmt->bindParam(':id_produit', $idProduit);
                $stmt->execute();
                $modifProduitTaille = $stmt->rowCount();
            } else {
                $_SESSION['erreur'] = "Veuillez remplir tous les champs obligatoires.";
            }
        }

        $modifPierres = 0;
        if (isset($_POST['matiere']) && is_array($_POST['matiere'])) {
            foreach ($_POST['matiere'] as $id_sup => $matiere) {
                $couleur = $_POST['couleur'][$id_sup] ?? null;
                $nombre = $_POST['nombre'][$id_sup] ?? null;
                $forme = $_POST['forme'][$id_sup] ?? null;
                $caratage = $_POST['caratage'][$id_sup] ?? null;
                $sertis = $_POST['sertis'][$id_sup] ?? null;

                $stmt = $conn->prepare("UPDATE produit_suplement SET matiere = :matiere, couleur = :couleur, 
                nombre = :nombre, forme = :forme, caratage = :caratage, sertis = :sertis WHERE id = :id_sup");

                $stmt->bindParam(':matiere', $matiere);
                $stmt->bindParam(':couleur', $couleur);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':forme', $forme);
                $stmt->bindParam(':caratage', $caratage);
                $stmt->bindParam(':sertis', $sertis);
                $stmt->bindParam(':id_sup', $id_sup, PDO::PARAM_INT);

                $stmt->execute();
                $modifPierres += $stmt->rowCount();
            }
        }

        $matiere_s = $_POST['matiere_s'] ?? null;
        $couleur_s = $_POST['couleur_s'] ?? null;
        $motif = $_POST['motif'] ?? null;
        $chaine = $_POST['chaine'] ?? null;
        $fermoir = $_POST['fermoir'] ?? null;

        $stmt = $conn->prepare("UPDATE produit SET prix = :prix, matiere_p = :matiere_p, couleur_p = :couleur_p, matiere_s = :matiere_s, couleur_s = :couleur_s, 
        motif = :motif, chaine = :chaine, fermoir = :fermoir, id_genre = :id_genre WHERE id = :id");
        $stmt->bindParam(':prix', $_POST['prix']);
        $stmt->bindParam(':matiere_p', $_POST['matiere_p']);
        $stmt->bindParam(':couleur_p', $_POST['couleur_p']);
        $stmt->bindParam(':matiere_s', $matiere_s);
        $stmt->bindParam(':couleur_s', $couleur_s);
        $stmt->bindParam(':motif', $motif);
        $stmt->bindParam(':chaine', $chaine);
        $stmt->bindParam(':fermoir', $fermoir);
        $stmt->bindParam(':id_genre', $_POST['genre']);
        $stmt->bindParam(':id', $idProduit);
        $stmt->execute();
        $modifProduit = $stmt->rowCount();

        if ($modifProduit > 0 || $modifProduitTaille > 0 || $modifPierres > 0) {
            $_SESSION['message'] = "Les modifications ont été appliquées avec succès";
            $query = http_build_query($_GET);
            header("Location: produit_page.php?" . $query);
            exit();
        } else {
            $_SESSION['erreur'] = "Veuillez modifier au moins un champ pour appliquer les modifications";
        }
    } else {
        $_SESSION['erreur'] = "Veuillez remplir tous les champs obligatoires";
    }

    
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../css/produit_page.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Titre personnalisé en fonction de l'article -->
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
    // Navbar
    include ('../include/navbar.php');

    // Popup d'ajout de taille
    ?>
    <div id="popupForm" class="popup">
        <div class="popup-content">
            <span id="closePopup" class="close">&times;</span>
            <h2>Ajouter une taille</h2>
            <form method="post">
                <?php if($produit['type_produit'] == 'Bague') { ?>
                    <div class="input-group-popup">
                        <label for="pop_tour_doigt">Tour de doigt</label>
                        <input type="number" name="pop_tour_doigt" min="42" max="76">
                    </div>
                <?php } else { ?>
                    <div class="input-group-popup">
                        <label for="pop_longueur">Longueur</label>
                        <input type="number" name="pop_longueur" min="0" step="0.01">
                    </div>
                    <div class="input-group-popup">
                        <label for="pop_largeur">Largeur</label>
                        <input type="number" name="pop_largeur" min="0" step="0.01">
                    </div>
                <?php } ?>
                <div class="input-group-popup">
                    <label for="pop_quantite">Quantité</label>
                    <input type="number" name="pop_quantite" min="0" step="1">
                </div>
                <div class="input-group-popup">
                    <label for="pop_poids">Poids (gr)</label>
                    <input type="number" name="pop_poids" min="0" step="0.001">
                </div>
                <button type="submit" name="ajouterTaille">Ajouter</button>
            </form>
        </div>
    </div>

    <?php
    // Si les infos produit sont trouvées
    if ($produit) { 
        // Récupération des images
        $stmt = $conn->prepare("SELECT image_chemin FROM produit_image WHERE id_produit = :id_produit");
        $stmt->bindParam(':id_produit', $idProduit);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- Bouton retour -->
    <div class="retour">
        <button onclick="history.back();" id="btnRetour">< Retour</button>
    </div>

    <!-- Message reussite et erreur -->
    <?php
    if (isset($_SESSION['message'])) {
        echo '<p id="succes"><i class=\'bx bxs-check-circle bx-sm\'></i>' . $_SESSION['message'] . '</p>';
        unset($_SESSION['message']);
    } else if (isset($_SESSION['erreur'])) {
        echo '<p id="erreur"><i class=\'bx bxs-error-circle bx-sm\'></i>' . $_SESSION['erreur'] . '</p>';
        unset($_SESSION['erreur']);
    }
    ?>

    <div class="produit">
        <!-- Carousel des images du produit -->
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

        <!-- Infos princales du produit -->
        <div class="infos-produit">
            <div class="haut-desc">
                <h1><?php echo htmlspecialchars($produit['type_produit']) ." ". htmlspecialchars($produit['motif']) ." ". htmlspecialchars($produit['matiere_p']) ." ". htmlspecialchars($produit['couleur_p'] ." ". $produit['matiere_s'] ." ". $produit['couleur_s'] ." ". $produit['forme'] ." ". $produit['matiere'] ." ". $produit['couleur']) ?></h1>
                <h3><?php echo htmlspecialchars($produit['prix']) . " €";?></h3>
            </div>

            <!-- Avis -->
            <div class="avis">
                <i class='bx bxs-star'></i>
                <i class='bx bxs-star'></i> 
                <i class='bx bxs-star'></i>
                <i class='bx bxs-star'></i>
                <i class='bx bx-star'></i>
                <p>4.2</p>
                <a href="#">(21 avis)</a>
            </div>

            <!-- Recherche du vendeur du produit -->
            <div class="vendeur">
                <?php 
                $stmt = $conn->prepare("SELECT entreprise FROM fournisseur WHERE id = :id");
                $stmt->bindParam(':id', $produit['id_fournisseur']);
                $stmt->execute();
                $vendeur = $stmt->fetch();
                ?>
                <h4>Produit fabriqué et vendu par <span><?php echo htmlspecialchars($vendeur['entreprise']); ?></h4>
            </div>

            <!-- Récupération des tailles du produit -->
            <div class="tailles">
                <?php if (isset($_SESSION['erreurTaille'])) {
                    echo '<p id="erreur">' . $_SESSION['erreurTaille'] . '</p>';
                    unset($_SESSION['erreurTaille']);
                } ?> 
                <div class="taille-grid">
                    <?php 
                    $sql = "SELECT * FROM produit_taille WHERE id_produit = :id_produit";
                    if($produit['type_produit'] == 'Bague') {
                        $sql .= " ORDER BY tour_doigt ASC";
                    }
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id_produit', $idProduit);
                    $stmt->execute();
                    $tailles = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if (count($tailles) === 1) { // Si il y a qu'une seule taille
                        $tailleUnique = $tailles[0]; 
                        ?>
                        <a href="#" id="taille active" class="taille active">Taille unique</a>
                        <?php
                    } else { // Sinon une boucle avec les tailles
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
                    <button id="tailleAdd" class="taille add" name="ajouterTaille"><i class='bx bx-plus'></i></button>
                </div>
                
                <?php // Recherche de la taille active pour afficher un message d'information
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

                <!-- Supprimer l'article définitivement -->
                <div class="panier-ajout">
                    <form method="POST" onsubmit="return confirm('Voulez-vous vraiment supprimer cet article définitivement ?');">
                        <input type="hidden" name="idProduit" value="<?php echo htmlspecialchars($idProduit) ;?>">
                        <button type="submit" name="supprimerArticle" class="btn-suppr">Supprimer l'article</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modification de l'article par un formulaire -->
    <form method="POST" id="modifArticle">
        <!-- PARTIE 1 -->
        <div class="produit-desc">
            <div class="desc-group">
                <!-- Colonne 1 | Détail produit -->
                <h2>Détail produit</h2>
                <div class="donnee-group">
                    <h5>Genre</h5> 
                    <select name="genre">
                        <?php if($produit['id_genre'] == 1) { ?>
                            <option value="1" selected>Femme</option>
                            <option value="2">Homme</option>
                        <?php } else { ?>
                            <option value="1">Femme</option>
                            <option value="2" selected>Homme</option>
                        <?php } ?>
                    </select>
                </div>
                <?php if(!empty($tailleActive['tour_doigt'])) { ?>
                    <div class="donnee-group"><h5>Tour de doigt*</h5> <input type="number" min="42" max="76" step="2" name="tour_doigt" value="<?php echo $tailleActive['tour_doigt']; ?>"></div>
                <?php } ?>
                <div class="donnee-group"><h5>Quantité</h5> <input type="number" name="quantite" value="<?php echo $tailleActive['quantite']; ?>"></div>
                <div class="donnee-group"><h5>Prix*</h5> <input type="number" name="prix" step="0.01" value="<?php echo $produit['prix']; ?>"></div>
                <div class="donnee-group"><h5>Poids total (gr)*</h5> <input type="number" step="0.001" name="poids" value="<?php echo $tailleActive['poids']; ?>"></div>
                <div class="donnee-group"><h5>Matière principale*</h5> <input type="text" name="matiere_p" value="<?php echo $produit['matiere_p']; ?>"></div>
                <div class="donnee-group"><h5>Couleur principale*</h5> <input type="text" name="couleur_p" value="<?php echo $produit['couleur_p']; ?>"></div>
                <?php if(!empty($produit['matiere_s'])) { ?>
                    <div class="donnee-group"><h5>Matière secondaire</h5> <input type="text" name="matiere_s" value="<?php echo $produit['matiere_s']; ?>"></div>
                    <div class="donnee-group"><h5>Couleur secondaire</h5> <input type="text" name="couleur_s" value="<?php echo $produit['couleur_s']; ?>"></div>
                <?php } 
                if(!empty($produit['titrage'])) { ?>
                    <div class="donnee-group"><h5>Titrage</h5> <input type="number" min="0" max="1000" step="1" name="titrage" value="<?php echo $produit['titrage']; ?>"></div>
                <?php }
                if(!empty($produit['motif'])) { ?>
                    <div class="donnee-group"><h5>Forme</h5> <input type="text" name="motif" value="<?php echo $produit['motif']; ?>"></div>
                <?php }
                if(!empty($produit['chaine'])) { ?>
                    <div class="donnee-group"><h5>Type de chaine</h5> <input type="text" name="chaine" value="<?php echo $produit['chaine']; ?>"></div>
                <?php } 
                if(!empty($tailleActive['longueur']) && !empty($tailleActive['largeur'])) { ?>
                    <div class="donnee-group"><h5>Longueur</h5> <input type="number" min="0" step="0.01" name="longueur" value="<?php echo $tailleActive['longueur']; ?>"></div>
                    <div class="donnee-group"><h5>Largeur</h5> <input type="number" min="0" step="0.01" name="largeur" value="<?php echo $tailleActive['largeur']; ?>"></div>
                <?php } 
                if(!empty($produit['fermoir'])) {?>
                    <div class="donnee-group"><h5>Type de fermoir</h5> <input type="text" name="fermoir" value="<?php echo $produit['fermoir']; ?>"></div>
                <?php } ?>
            </div>

            <!-- Colonne 2 (et 3 en fonction) | Pierres et pendentif -->
            <?php 
            $stmt = $conn->prepare("SELECT * FROM produit_suplement WHERE id_produit = :id_produit");
            $stmt->bindParam(':id_produit', $idProduit);
            $stmt->execute();
            $pierres = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (count($pierres) > 0) {
               foreach($pierres as $pierre) { ?>
                    <div class="desc-group">
                        <h2><?php echo $pierre['type_sup']; ?></h2>
                        <div class="donnee-group"><h5>Type de pierre</h5> <input type="text" name="matiere[<?php echo $pierre['id']; ?>]" value="<?php echo $pierre['matiere']; ?>"></div>
                        <div class="donnee-group"><h5>Couleur</h5> <input type="text" name="couleur[<?php echo $pierre['id']; ?>]" value="<?php echo $pierre['couleur']; ?>"></div>
                        <?php if(!empty($pierre['nombre'])) { ?>
                            <div class="donnee-group"><h5>Nombres de pierres</h5> <input type="number" name="nombre[<?php echo $pierre['id']; ?>]" value="<?php echo $pierre['nombre']; ?>"></div>
                        <?php } ?>
                        <?php if(!empty($pierre['forme'])) { ?>
                            <div class="donnee-group"><h5>Forme du pendentif</h5> <input type="text" name="forme[<?php echo $pierre['id']; ?>]" value="<?php echo $pierre['forme']; ?>"></div>
                        <?php } ?>
                        <?php if(!empty($pierre['caratage'])) { ?>
                            <div class="donnee-group"><h5>Caratage</h5> <input type="number" name="caratage[<?php echo $pierre['id']; ?>]" value="<?php echo $pierre['caratage']; ?>"></div>
                        <?php } ?>
                        <?php if(!empty($pierre['sertis'])) { ?>
                            <div class="donnee-group"><h5>Type de sertis</h5> <input type="text" name="sertis[<?php echo $pierre['id']; ?>]" value="<?php echo $pierre['sertis']; ?>"></div>
                        <?php } ?>
                    </div>
                <?php } 
            } ?> 
            
        
        </div>
        <div class="button-part">
            <button type="submit" name="modifierArticle" id="modifBtn">Appliquer les modifications</button>
        </div>
    </form>
    <?php 
    } else { ?>
        <div class="article-introuvable">
            <div class="bulle">
                <i class='bx bxs-info-circle bx-lg'></i>
                <p>Article introuvable</p>
                <a href="inventaire.php">< Retour</a>
            </div>
        </div>
    <?php }
    include ('../include/footer.php');
    ?>

    <!------------------------ LIAISONS JS ------------------------>
    <script src="../js/slider_article.js"></script>

    <script>
        const popup = document.getElementById("popupForm");
        const openButton = document.getElementById("tailleAdd");
        const closeButton = document.getElementById("closePopup");

        openButton.addEventListener("click", () => {
        popup.style.display = "flex";
        });

        closeButton.addEventListener("click", () => {
        popup.style.display = "none";
        });

        window.addEventListener("click", (event) => {
        if (event.target === popup) {
            popup.style.display = "none";
        }
        });

    </script>

</body>
</html>

