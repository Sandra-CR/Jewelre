<!------------ Démarrage de session et liaison à la BDD ------------>
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

//------------ Fermer l'acces a la page aux utilisateurs non connectés ------------>
if (!isset($_SESSION['entreprise']) && !isset($_SESSION['pseudo'])) {
    header("Location: login_client.php");
    exit();
} 
?>

<!------------------------------------------------------------------------------>
<!------------ RECUPERATION DES DONNEES DU FORMULAIRE ET TRAITEMENT ------------>
<!------------------------------------------------------------------------------>
<?php
if(isset($_POST['updateVendeur']) || isset($_POST['updateClient'])) {
    // en fonction du type d'utilisateur
    if(isset($_SESSION['entreprise'])) {
        $newNomE = htmlspecialchars(trim($_POST['entreprise']));
        $newPays = !empty($_POST['pays']) ? htmlspecialchars(trim($_POST['pays'])) : null;
    } else if(isset($_SESSION['pseudo'])) {
        $newPseudo = htmlspecialchars(trim($_POST['pseudo']));
        $newNom = !empty($_POST['nom']) ? htmlspecialchars(trim($_POST['nom'])) : null;
        $newPrenom = !empty($_POST['prenom']) ? htmlspecialchars(trim($_POST['prenom'])) : null;
    }

    // données communes
    $newEmail = htmlspecialchars(trim($_POST['email']));
    $modifMdp = htmlspecialchars(trim($_POST['mdp']));
    $modifMdpC = htmlspecialchars(trim($_POST['mdpC']));

    // vérification du mot de passe
    if($modifMdp != null && $modifMdpC != null) {
        if($modifMdp == $modifMdpC) {
            $newMdp = password_hash($modifMdp, PASSWORD_DEFAULT);
        } else {
            $_SESSION['erreur'] = 'Les mots de passe ne correspondent pas';
            header("Location: profil.php");
            exit();
        }
    } else if(empty($modifMdp) && empty($modifMdpC)) {
        $newMdp = null;
    } else {
        $_SESSION['erreur'] = 'Veuillez remplir les deux champs pour changer le mot de passe';
        header("Location: profil.php");
        exit();
    } ?>
    
    <!------------------------------------------------------------------->
    <!------------ REQUETE UPDATE ET PARAMETRAGE DES DONNEES ------------>
    <!------------------------------------------------------------------->
    <?php try {
        // pour les vendeurs
        if(isset($_SESSION['entreprise'])) {
            $stmt = $conn->prepare("SELECT * FROM fournisseur WHERE entreprise = :entreprise");
            $stmt->bindParam(':entreprise', $_SESSION['entreprise']);
            $stmt->execute();
            $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $query = "UPDATE fournisseur SET entreprise = :entreprise, email = :email" . 
            ($newPays ? ", pays = :pays" : "") . 
            ($newMdp ? ", mdp = :mdp" : "") . 
            " WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':entreprise', $newNomE);
            $stmt->bindParam(':email', $newEmail);
            if($newPays) {
                $stmt->bindParam(':pays', $newPays);
            }
            if($newMdp) {
                $stmt->bindParam(':mdp', $newMdp);
            }
            $stmt->bindParam(':id', $vendeur['id']);
            $stmt->execute();
            $_SESSION['entreprise'] = $newNomE;

            if (isset($_POST['updateVendeur'])) {
                if (!empty($_FILES['logo']['name'])) {
                    $uploadDir = '../img/profile/';
                    $logoTmpName = $_FILES['logo']['tmp_name'];
                    $logoName = basename($_FILES['logo']['name']);
                    $cheminFichier = $uploadDir . uniqid() . '_' . $logoName;
            
                    $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
                    $fileExtension = strtolower(pathinfo($logoName, PATHINFO_EXTENSION));
            
                    if (in_array($fileExtension, $validExtensions)) {
                        if (move_uploaded_file($logoTmpName, $cheminFichier)) {
                            if (!empty($vendeur['logo'])) {
                                $ancienLogo = $vendeur['logo'];
                                if (file_exists($uploadDir . $ancienLogo)) {
                                    unlink($uploadDir . $ancienLogo);
                                }
                            }
            
                            $sql = "UPDATE fournisseur SET logo = :logo WHERE id = :id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':logo', $cheminFichier);
                            $stmt->bindParam(':id', $_SESSION['id']);
                            if ($stmt->execute()) {
                                echo "<p><span>Logo mis à jour avec succès !</span></p>";
                            } else {
                                echo "<p><span>Erreur lors de la mise à jour du logo dans la base de données.</span></p>";
                            }
                        } else {
                            echo "<p><span>Erreur lors du téléchargement de l'image : $logoName</span></p>";
                        }
                    } else {
                        echo "<p><span>Extension de fichier non valide. Seuls les fichiers JPG, JPEG, PNG et WEBP sont autorisés.</span></p>";
                    }
                } else {
                    echo "<p><span>Aucun fichier sélectionné.</span></p>";
                }
            }
            
            
            


        // pour les clients
        } else if(isset($_SESSION['pseudo'])) {
            $stmt = $conn->prepare("SELECT * FROM client WHERE pseudo = :pseudo");
            $stmt->bindParam(':pseudo', $_SESSION['pseudo']);
            $stmt->execute();
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            $clientId = $client['id'];

            $query = "UPDATE client SET pseudo = :pseudo" . 
            ($newNom ? ", nom = :nom" : "") . 
            ($newPrenom ? ", prenom = :prenom" : "") . 
            ", email = :email" . 
            ($newMdp ? ", mdp = :mdp" : "") . 
            " WHERE id = :id";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':pseudo', $newPseudo);
            if($newNom) {
                $stmt->bindParam(':nom', $newNom);
            }
            if($newPrenom) {
                $stmt->bindParam(':prenom', $newPrenom);
            }
            $stmt->bindParam(':email', $newEmail);
            if($newMdp) {
                $stmt->bindParam(':mdp', $newMdp);
            }
            $stmt->bindParam(':id', $client['id']);
            $stmt->execute();
            $_SESSION['pseudo'] = $newPseudo;
        }
        
        // affichage de confirmation et redirection
        $_SESSION['succes'] = 'Les changements ont été pris en compte';
        header("Location: profil.php");
        exit();
    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        exit();
    }


    // if(isset($_POST['ajout-adresse'])) {
        if (isset($_POST['ajout-adresse']) && !empty($_POST['pays']) && !empty($_POST['ville']) && !empty($_POST['rue']) && !empty($_POST['numero'])) {
            try {
                $stmt = $conn->prepare("INSERT INTO adresse (pays, ville, rue, numero, active, id_client) VALUES (:pays, :ville, :rue, :numero, :active, :id_client)");
                $stmt->bindParam(':pays', $_POST['pays']);
                $stmt->bindParam(':ville', $_POST['ville']);
                $stmt->bindParam(':rue', $_POST['rue']);
                $stmt->bindParam(':numero', $_POST['numero']);
                $stmt->bindParam(':numero', 0);
                $stmt->bindParam(':id_client', $clientId);
                $stmt->execute();
        
                header("Location: profil.php");
                exit();
            } catch (PDOException $e) {
                die("Erreur SQL : " . $e->getMessage());
            }
        } else {
            echo "Tous les champs doivent être remplis!";
        }
    // }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/profil.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <title>Mon profil | Jewelr-e</title>
</head>
<body>
    <!------------ Appel de la navbar ------------>
    <?php include('../include/navbar.php'); ?>

    <!---------------------------------------------------------------------->
    <!------------ FORMULAIRE ET APPEL DES DONNEES UTILISATEURS ------------>
    <!---------------------------------------------------------------------->
    <div class="profile-contenu" id="profilSection">
        <!-- Bouton retour -->
        <div class="retour">
            <button onclick="history.back();" id="btnRetour">< Retour</button>
        </div>
        <!-- pour les vendeurs -->
        <?php if(!empty($_SESSION['entreprise'])) {
            $stmt = $conn->prepare("SELECT * FROM fournisseur WHERE entreprise = :entreprise");
            $stmt->bindParam(':entreprise', $_SESSION['entreprise']);
            $stmt->execute();
            $vendeur = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vendeur) {
                echo "Aie... Nous rencontrons un problème. Veuillez réessayer plus tard.";
                exit();
            }

            $dateFr = DateTime::createFromFormat('Y-m-d', $vendeur['date_creation'])->format('d/m/Y');
            ?>
            <form action="profil.php" method="POST" class="profil-form">

                <!-- PARTIE GAUCHE PROFILE -->
                <div class="left-profile">
                    <div class="image-profile">
                        <img src="<?php echo !empty($vendeur['logo']) && file_exists('../img/profile/' . $vendeur['logo']) ? '../img/profile/' . $vendeur['logo'] : '../img/profile/default1.png'; ?>" alt="Logo d'entreprise">
                        <input type="file" name="logo" id="fileInput" class="custom-file-input">
                        <label for="fileInput" class="custom-file-label"><i class='bx bxs-pencil bx-sm'></i></label>
                    </div>
                </div>

                <!-- PARTIE DROITE PROFILE -->
                <div class="right-profile">
                    <div class="titre-group">
                        <h2>Mon profil</h2>
                        <h4><?php echo $dateFr; ?></h4>
                    </div>

                    <!-- appel des messages (erreur et succes) -->
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

                    <!-- les inputs du formulaire -->
                    <input type="hidden" value="<?php echo $vendeur['id']; ?>">
                    <div class="info-group">
                        <label for="entreprise">Nom d'entreprise</label>
                        <input type="text" name="entreprise" value="<?php echo $vendeur['entreprise']; ?>" required>
                    </div>
                    <div class="info-group">
                        <label for="email">Adresse mail</label>
                        <input type="email" name="email" value="<?php echo $vendeur['email']; ?>" required>
                    </div>
                    <div class="info-group">
                        <label for="pays">Pays</label>
                        <input type="text" name="pays" value="<?php echo $vendeur['pays']; ?>" placeholder="non renseigné">
                    </div>
                    <br>
                    <div class="info-group mdp">
                        <label for="mdp">Changer le mot de passe</label>
                        <input type="password" name="mdp" id="mdp">
                    </div>
                    <div class="info-group cfm">
                    <label for="mdpC">Confirmer</label>
                        <input type="password" name="mdpC" id="mdpC">
                    </div>
                    <div class="afficher-mdp">
                        <input type="checkbox">
                        <p>Afficher le mot de passe</p>
                    </div>
                    <br>
                    <div class="btn-update">
                        <button type="submit" name="updateVendeur">Appliquer</button>
                    </div>
                </div>
            </form>
    
        <!-- pour les clients -->
        <?php } else if(!empty($_SESSION['pseudo'])) {
            $stmt = $conn->prepare("SELECT * FROM client WHERE pseudo = :pseudo");
            $stmt->bindParam(':pseudo', $_SESSION['pseudo']);
            $stmt->execute();
            $client = $stmt->fetch(PDO::FETCH_ASSOC);
            $dateFr = DateTime::createFromFormat('Y-m-d', $client['date_creation'])->format('d/m/Y');
            ?>
            <form action="profil.php" method="POST" class="profil-form">

                <!-- PARTIE GAUCHE PROFILE -->
                <div class="left-profile">
                    <div class="image-profile">
                        <img src="<?php echo !empty($client['pdp']) && file_exists('../img/profile/' . $client['pdp']) ? '../img/profile/' . $client['pdp'] : '../img/profile/default1.png'; ?>" alt="Photo de profil">
                        <input type="file" name="pdp" id="fileInput" class="custom-file-input">
                        <label for="fileInput" class="custom-file-label"><i class='bx bxs-pencil bx-sm'></i></label>
                    </div>
                </div>

                <!-- PARTIE DROITE PROFILE -->
                <div class="right-profile">
                    <div class="titre-group">
                        <h2>Mon profil</h2>
                        <h4><?php echo $dateFr; ?></h4>
                    </div>

                    <!-- appel des messages (erreur et succes) -->
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

                    <!-- les inputs du formulaire -->
                    <input type="hidden" value="<?php echo $client['id']; ?>">
                    <div class="info-group">
                        <label for="pseudo">Nom d'utilisateur</label>
                        <input type="text" name="pseudo" value="<?php echo $client['pseudo']; ?>" required>
                    </div>
                    <div class="info-group">
                        <label for="nom">Nom</label>
                        <input type="text" name="nom" value="<?php echo $client['nom']; ?>">
                    </div>
                    <div class="info-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" name="prenom" value="<?php echo $client['prenom']; ?>">
                    </div>
                    <div class="info-group">
                        <label for="email">Adresse mail</label>
                        <input type="email" name="email" value="<?php echo $client['email']; ?>" required>
                    </div>
                    <br>
                    <div class="info-group mdp">
                        <label for="mdp">Changer le mot de passe</label>
                        <input type="password" name="mdp" id="mdp">
                    </div>
                    <div class="info-group cfm">
                    <label for="mdpC">Confirmer</label>
                        <input type="password" name="mdpC" id="mdpC">
                    </div>
                    <div class="afficher-mdp">
                        <input type="checkbox">
                        <p>Afficher le mot de passe</p>
                    </div>
                    <br>
                    <div class="btn-update">
                        <button type="submit" name="updateClient">Appliquer</button>
                    </div>
                </div>
            </form>
        <?php } ?>
    </div>

    <?php if(!empty($_SESSION['pseudo'])) { ?>
        <div class="profile-contenu" id="adresse">
            <?php 
            $stmt = $conn->prepare("SELECT * FROM adresse WHERE id_client = :id_client AND active IS NOT NULL ORDER BY active DESC");
            $stmt->bindParam(':id_client', $client['id']);
            $stmt->execute();
            $adresses = $stmt->fetchAll(PDO::FETCH_ASSOC);

            ?> <div class="titre">
                <h2>Mes adresses</h2>
                <p id="nb"><?php echo count($adresses); ?>/3</p>
            </div> <?php 

            if($adresses) {
                $number = 1;
                foreach($adresses as $adresse) { ?>
                    <div class="adresse-group">
                        <div class="text-adresse">
                            <p id="numberAdresse"><?php echo $number; ?></p>
                            <h4 id="adresseDisplay"><?php echo $adresse['numero'] .' '. $adresse['rue'] .', '. $adresse['ville'] .' <span id="uppercase">('. $adresse['pays'] .')</span>'; ?></h4>
                        </div>
                        <div class="buttons-adresse">
                            <form method="POST">
                                <input type="hidden" value="<?php echo $adresse['id']; ?>" name="thisAdresse">
                                <!-- Mettre une adresse en actif -->
                            </form>
                            <form method="POST">
                                <input type="hidden" value="<?php echo $adresse['id']; ?>" name="thisAdresseSuppr">
                                <button type="button" name="supprAdresse" id="supprAdresse"><i class='bx bxs-trash-alt'></i></button>
                            </form>
                        </div>
                    </div> 
                    <?php $number++;
                }
            }
            
            if(count($adresses) < 3) { ?>
                <form method="POST">
                    <button type="button" id="ajouterAdresseToggle" name="ajouterAdresseToggle"><i class='bx bx-plus'></i> Ajouter une adresse</button>
                </form>
            <?php } ?>

            <div id="popupForm" class="popup">
                <div class="popup-content">
                    <span id="closePopup" class="close">&times;</span>
                    <h2>Ajouter une adresse</h2>
                    <form method="post" action="">
                        <div class="form-content-adresse">
                            <div class="input-group-popup">
                                <label for="numero">N°</label>
                                <input name="numero" type="number" step="1" min="1">
                            </div>
                            <div class="input-group-popup">
                                <label for="rue">Rue</label>
                                <input name="rue" type="text">
                            </div>
                            <div class="input-group-popup">
                                <label for="ville">Ville</label>
                                <input name="ville" type="text">
                            </div>
                            <div class="input-group-popup">
                                <label for="pays">Pays</label>
                                <input name="pays" type="text">
                            </div>
                        </div>
                        <button id="ajoutAdresse" class="ajout-adresse" name="ajout-adresse" type="submit">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="profile-contenu" id="commandesSection">
            <?php 
            $stmt = $conn->prepare("SELECT * FROM commande WHERE id_client = :id_client ORDER BY id DESC");
            $stmt->bindParam(':id_client', $_SESSION['id']);
            $stmt->execute();
            $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            ?> <div class="titre">
                <h2>Mes commandes</h2>
                <p id="nb"><?php echo "Vous avez passé " . count($commandes); ?> <?php echo count($commandes) > 1 ? " commandes" : " commande"; ?></p>
            </div> <?php 

            if(!empty($commandes)) {
                foreach($commandes as $commande) {
                    $stmt = $conn->prepare("SELECT * FROM commande_contenu WHERE id_produit IN (SELECT id FROM produit) AND id_commande = :id_commande");
                    $stmt->bindParam(':id_commande', $commande['id']);
                    $stmt->execute();
                    $contenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <div class="commande-item">
                        <div class="commande-title">
                            <form action="../../model/facture.php" method="POST">
                                <div class="group">
                                    <input type="hidden" name="idCommande" value="<?php echo $commande['id']; ?>">
                                    <button type="submit" class="pdf"><i class='bx bxs-file-pdf'></i></button>
                                    <h3>Commande N°<?php echo $commande['id']; ?></h3>
                                </div>
                            </form>
                                <p id="prixTotal"><?php echo $commande['prix_total'] . "€"; ?></p>
                        </div>

                        <?php 
                        $calculPrixTotal = 0;
                        foreach($contenus as $contenu) {
                            $stmt = $conn->prepare("SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.prix, p.matiere_s, p.couleur_s, c.titre, g.genre,
                            GROUP_CONCAT(
                            CONCAT(ps.matiere, ' ', ps.couleur) 
                            ORDER BY ps.id ASC SEPARATOR ' et '
                            ) AS pierres_info,
                            (SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin
                            FROM produit p
                            LEFT JOIN produit_suplement ps ON ps.id_produit = p.id
                            LEFT JOIN collection c ON c.id = p.id_collection
                            LEFT JOIN genre g ON g.id = p.id_genre
                            WHERE p.id = :id");
                            $stmt->bindParam(':id', $contenu['id_produit']);
                            $stmt->execute();
                            $produit = $stmt->fetch(PDO::FETCH_ASSOC);
                            $pierresInfo = $produit['pierres_info'];
                            $calculPrixTotal += $produit['prix']; 
                            ?>

                            <div class="produit-commande">
                                <?php if(!empty($produit)) { ?>
                                    <div class="produit-group">
                                        <img src="<?php echo $produit['image_chemin']; ?>" alt="#">
                                        <h4><?php echo $produit['type_produit'] . " " . $produit['motif'] . " " . $produit['matiere_p'] . " " . $produit['couleur_p'] . " " . $produit['matiere_s'] . " " . $produit['couleur_s'] . " " . $pierresInfo; ?></h4>
                                    </div>
                                    <p id="prixProduit"><?php echo $produit['prix'] . "€"; ?></p>
                                <?php } ?>
                            </div>
                        <?php } 

                        if($commande['prix_total'] > $calculPrixTotal) { ?>
                            <div class="produit-commande">
                                <p id="message"><i class='bx bxs-error-circle bx-sm'></i> Un ou plusieurs articles de votre commande ont été supprimés (valeur: <?php echo $commande['prix_total'] - $calculPrixTotal; ?>€)</p>
                            </div>  
                        <?php } ?>
                        
                    </div>

                <?php 
                }
            } else { ?>
                <p id="message"><i class='bx bxs-error-circle bx-sm'></i> Aucune commande n'a encore été passée</p>
                
            <?php } ?>       
        </div>
    <?php } ?>
    <?php include('../include/footer.php'); ?>

    <!--------------------------------------------------------->
    <!------------ SCRIPT AFFICHER LE MOT DE PASSE ------------>
    <!--------------------------------------------------------->
    <script>
    let input = document.querySelector('.info-group.mdp input');
    let confi = document.querySelector('.info-group.cfm input');
    let checkbox = document.querySelector('.afficher-mdp input');
    checkbox.onclick =function(){
            if(input.type === "password") {
            input.type = "text";
            confi.type = "text";
        } else {
            input.type = "password";
            confi.type = "password";
        }
    } 
    </script>

    <script>
        const popup = document.getElementById("popupForm");
        const openButtonAdd = document.getElementById("ajouterAdresseToggle");
        const closeButton = document.getElementById("closePopup");

        openButtonAdd.addEventListener("click", () => {
            event.preventDefault();
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

        document.querySelectorAll('.close').forEach(button => {
            button.addEventListener("click", (event) => {
                const popup = document.getElementById(`popupForm`);
                popup.style.display = "none";
            });
        });

        window.addEventListener("click", (event) => {
            if (event.target.classList.contains('popup')) {
                event.target.style.display = "none";
            }
        });
    </script>
</body>
</html>