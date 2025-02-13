<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include ('bdd.php');
        
        try {
            // CONNEXION
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // DONNEES PRODUIT
            $type = "Boucles d'oreilles";
            $genre = $_POST['genre'];
            $matierep = htmlspecialchars(trim($_POST['matierep']));
            $couleurp = htmlspecialchars(trim($_POST['couleurp']));
            $matieres = htmlspecialchars(trim($_POST['matieres']));
            $couleurs = htmlspecialchars(trim($_POST['couleurs']));
            $titrage = $_POST['titrage'];
            $motif = htmlspecialchars(trim($_POST['motif']));
            $fermoir = htmlspecialchars(trim($_POST['fermoir']));
            $prix = $_POST['prix'];
            if(isset($_POST['envente'])) { $envente = 1; } else { $envente = 0; }
            
            // DONNEES PRODUIT_SUPLEMENT P
            $pmatiere = htmlspecialchars(trim($_POST['pmatiere']));
            $pcouleur = htmlspecialchars(trim($_POST['pcouleur']));
            $pnombre = $_POST['pnombre'];
            $pcaratage = $_POST['pcaratage'];
            $psertis = htmlspecialchars(trim($_POST['psertis']));

            // DONNEES PRODUIT_SUPLEMENT S
            $smatiere = htmlspecialchars(trim($_POST['smatiere']));
            $scouleur = htmlspecialchars(trim($_POST['scouleur']));
            $snombre = $_POST['snombre'];
            $scaratage = $_POST['scaratage'];
            $ssertis = htmlspecialchars(trim($_POST['ssertis']));

            // DONNEES PRODUIT_TAILLE
            $qt1 = $_POST['qt1'];
            $largeur = $_POST['largeur'];
            $longueur = $_POST['longueur'];
            $poids = $_POST['poids'];

            // CONDITION champs* caractéristiques
            if($genre === "" || $matierep === "" || $couleurp === "" || $fermoir === "" || $prix === "" || empty($_FILES['images']['name'][0])) {
                echo "<p><span>Certains champs obligatoires sont vides</span></p>";
            } else {
                // CONDITION pierre principale cochée
                if(isset($_POST['checkp'])) {
                    // CONDITION champs* de pierre principale ne sont pas remplis
                    if($pmatiere === "" || $pcouleur === "" || $pnombre === "" || $psertis === "") {
                        echo "<p><span>L'option de pierre principale est cochée mais les champs obligatoires ne sont pas tous remplis</span></p>";
                    } else {
                        // CONDITION pierre secondaire coché
                        if(isset($_POST['checks'])) {
                            // CONDITION champs* de pierre secondaire ne sont pas remplis
                            if($smatiere === "" || $scouleur === "" || $snombre === "" || $ssertis === "") {
                                echo "<p><span>L'option de pierre secondaire est cochée mais les champs obligatoires ne sont pas tous remplis</span></p>";
                            } else {
                                //
                                // CODE AVEC PIERRE PRINCIPALE ET SECONDAIRE
                                //
                                if(!isset($_POST['engagement'])) {
                                    echo "<p><span>Veuillez accepter la condition obligatoire</span></p>";
                                } else {
                                    if(isset($_POST['envente']) && ($qt1 === "" || $qt1 === '0')) {
                                        echo "<p><span>L'article ne peut pas etre mis en vente immédiatement sans quantité</span></p>";
                                    } else {
                                        $id_produit = ajouterProduit($conn, $type, $prix, $envente, $titrage, $matierep, $couleurp, $matieres, $couleurs, $motif, $fermoir, $genre, $_SESSION['idVendeur']);
                                        if (!empty($_FILES['images']['name'][0])) {
                                            $uploadDir = '../img/boutique/';
                                            foreach ($_FILES['images']['name'] as $key => $name) {
                                                $tmpName = $_FILES['images']['tmp_name'][$key];
                                                $cheminFichier = $uploadDir . uniqid() . '_' . time() . '_' . basename($name);
                                                if (move_uploaded_file($tmpName, $cheminFichier)) {
                                                    ajouterImages($conn, $cheminFichier, $id_produit);
                                                } else {
                                                    echo "<p><span>Erreur lors du téléchargement de l'image : $name</span></p>";
                                                }
                                            }
                                        }
                                        ajouterPierreP($conn, $pmatiere, $pcouleur, $pcaratage, $psertis, $pnombre, $id_produit);
                                        ajouterPierreS($conn, $smatiere, $scouleur, $scaratage, $ssertis, $snombre, $id_produit);
                                        if($qt1 !== "") {
                                            ajouterQuantite($conn, $qt1, $longueur, $largeur, $poids, $id_produit);
                                        }
                                        header("Location: add_earrings.php");
                                        exit();
                                    }
                                }
                            }
                        } else {
                            //
                            // CODE AVEC PIERRE PRINCIPALE SEULEMENT
                            //
                            if(!isset($_POST['engagement'])) {
                                echo "<p><span>Veuillez accepter la condition obligatoire</span></p>";
                            } else {
                                if(isset($_POST['envente']) && ($qt1 === "" || $qt1 === '0')) {
                                    echo "<p><span>L'article ne peut pas etre mis en vente immédiatement sans quantité</span></p>";
                                } else {
                                    $id_produit = ajouterProduit($conn, $type, $prix, $envente, $titrage, $matierep, $couleurp, $matieres, $couleurs, $motif, $fermoir, $genre, $_SESSION['idVendeur']);
                                    if (!empty($_FILES['images']['name'][0])) {
                                        $uploadDir = '../img/boutique/';
                                        foreach ($_FILES['images']['name'] as $key => $name) {
                                            $tmpName = $_FILES['images']['tmp_name'][$key];
                                            $cheminFichier = $uploadDir . uniqid() . '_' . time() . '_' . basename($name);
                                            if (move_uploaded_file($tmpName, $cheminFichier)) {
                                                ajouterImages($conn, $cheminFichier, $id_produit);
                                            } else {
                                                echo "<p><span>Erreur lors du téléchargement de l'image : $name</span></p>";
                                            }
                                        }
                                    }
                                    ajouterPierreP($conn, $pmatiere, $pcouleur, $pcaratage, $psertis, $pnombre, $id_produit);
                                    if($qt1 !== "") {
                                        ajouterQuantite($conn, $qt1, $longueur, $largeur, $poids, $id_produit);
                                    }
                                    header("Location: add_earrings.php");
                                    exit();
                                }
                            }
                        }
                    }
                // CONDITION si pierre secondaire est cochée et non pierre principale
                } elseif(!isset($_POST['checkp']) && isset($_POST['checks'])) {
                    echo "<p><span>Il est impossible de cocher l'option de pierre secondaire sans l'option de pierre principale</span></p>";
                } else {
                    //
                    // CODE SANS PIERRES
                    //
                    if(!isset($_POST['engagement'])) {
                        echo "<p><span>Veuillez accepter la condition obligatoire</span></p>";
                    } else {
                        if(isset($_POST['envente']) && ($qt1 === "" || $qt1 === '0')) {
                            echo "<p><span>L'article ne peut pas etre mis en vente immédiatement sans quantité</span></p>";
                        } else {
                            $id_produit = ajouterProduit($conn, $type, $prix, $envente, $titrage, $matierep, $couleurp, $matieres, $couleurs, $motif, $fermoir, $genre, $_SESSION['idVendeur']);
                            if (!empty($_FILES['images']['name'][0])) {
                                $uploadDir = '../img/boutique/';
                                foreach ($_FILES['images']['name'] as $key => $name) {
                                    $tmpName = $_FILES['images']['tmp_name'][$key];
                                    $cheminFichier = $uploadDir . uniqid() . '_' . time() . '_' . basename($name);
                                    if (move_uploaded_file($tmpName, $cheminFichier)) {
                                        ajouterImages($conn, $cheminFichier, $id_produit);
                                    } else {
                                        echo "<p><span>Erreur lors du téléchargement de l'image : $name</span></p>";
                                    }
                                }
                            }
                            if($qt1 !== "") {
                                ajouterQuantite($conn, $qt1, $longueur, $largeur, $poids, $id_produit);
                            }
                            header("Location: add_earrings.php");
                            exit();
                        }
                    }
                }
            
            }
        } catch(PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }

    function ajouterProduit($conn, $type, $prix, $envente, $titrage, $matierep, $couleurp, $matieres, $couleurs, $motif, $fermoir, $genre, $idVendeur) {
        $stmt = $conn->prepare("INSERT INTO produit (type_produit, prix, en_vente, sortie, titrage, 
        matiere_p, couleur_p, matiere_s, couleur_s, motif, fermoir, id_genre, id_fournisseur) VALUES (:type_produit, :prix, :en_vente, 
        CURDATE(), :titrage, :matiere_p, :couleur_p, :matiere_s, :couleur_s, :motif, :fermoir, :genre, :vendeur)");
        $stmt->bindParam(':type_produit', $type);
        $stmt->bindParam(':prix', $prix);
        $stmt->bindParam(':en_vente', $envente);
        $stmt->bindParam(':titrage', $titrage);
        $stmt->bindParam(':matiere_p', $matierep);
        $stmt->bindParam(':couleur_p', $couleurp);
        $stmt->bindParam(':matiere_s', $matieres);
        $stmt->bindParam(':couleur_s', $couleurs);
        $stmt->bindParam(':motif', $motif);
        $stmt->bindParam(':fermoir', $fermoir);
        $stmt->bindParam(':genre', $genre);
        $stmt->bindParam(':vendeur', $_SESSION['idVendeur']);
        $stmt->execute();
        return $conn->lastInsertId();
    }

    function ajouterImages($conn, $cheminFichier, $id_produit) {
        $stmt = $conn->prepare("INSERT INTO produit_image (image_chemin, id_produit) VALUES (:image_chemin, :id_produit)");
        $stmt->bindParam(':image_chemin', $cheminFichier);
        $stmt->bindParam(':id_produit', $id_produit);
        $stmt->execute();
    }

    function ajouterPierreP($conn, $pmatiere, $pcouleur, $pcaratage, $psertis, $pnombre, $id_produit) {
        $stmtpp = $conn->prepare("INSERT INTO produit_suplement (type_sup, matiere, couleur, caratage, sertis, nombre, id_produit) VALUES 
        (:type_sup, :pmatiere, :pcouleur, :pcaratage, :psertis, :pnombre, :id_produit)");
        $stmtpp->bindValue(':type_sup', "Pierre principale");
        $stmtpp->bindParam(':pmatiere', $pmatiere);
        $stmtpp->bindParam(':pcouleur', $pcouleur);
        $stmtpp->bindParam(':pcaratage', $pcaratage);
        $stmtpp->bindParam(':psertis', $psertis);
        $stmtpp->bindParam(':pnombre', $pnombre);
        $stmtpp->bindParam(':id_produit', $id_produit);
        $stmtpp->execute();
    }

    function ajouterPierreS($conn, $smatiere, $scouleur, $scaratage, $ssertis, $snombre, $id_produit) {
        $stmtps = $conn->prepare("INSERT INTO produit_suplement (type_sup, matiere, couleur, caratage, sertis, nombre, id_produit) VALUES 
        (:type_sup, :smatiere, :scouleur, :scaratage, :ssertis, :snombre, :id_produit)");
        $stmtps->bindValue(':type_sup', "Pierre secondaire");
        $stmtps->bindParam(':smatiere', $smatiere);
        $stmtps->bindParam(':scouleur', $scouleur);
        $stmtps->bindParam(':scaratage', $scaratage);
        $stmtps->bindParam(':ssertis', $ssertis);
        $stmtps->bindParam(':snombre', $snombre);
        $stmtps->bindParam(':id_produit', $id_produit);
        $stmtps->execute();
    }

    function ajouterQuantite($conn, $qt1, $longueur, $largeur, $poids, $id_produit) {
        $stmt = $conn->prepare("INSERT INTO produit_taille (quantite, longueur, largeur, poids, id_produit) VALUES
        (:qt1, :longueur, :largeur, :poids, :id_produit)");
        $stmt->bindParam(':qt1', $qt1);
        $stmt->bindParam(':longueur', $longueur);
        $stmt->bindParam(':largeur', $largeur);
        $stmt->bindParam(':poids', $poids);
        $stmt->bindParam(':id_produit', $id_produit);
        $stmt->execute();
    }
?>