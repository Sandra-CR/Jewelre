<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    include ('bdd.php');
        
    try {
        // CONNEXION
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $titre = $_POST['titre'];
        $date_sortie = $_POST['date_sortie'];
        $produits = isset($_POST['produitItem']) ? $_POST['produitItem'] : [];
        $image_chemin = '';


        if(empty($titre) || empty($date_sortie)) {
            $_SESSION['erreur'] = "<span>Certains champs obligatoires sont vides</span>";
            header("Location: add_collection.php");
            exit();
        } else {
            if(!isset($_POST['engagement'])) {
                $_SESSION['erreur'] = '<span>Veuillez accepter la condition obligatoire pour procéder</span>';
                header("Location: add_collection.php");
                exit();
            } else {
                if(isset($_POST['envente']) && empty($produits)) {
                    $_SESSION['erreur'] = "<span>Veuillez ajouter au minimum un article si vous souhaitez mettre 
                    en vente la collection immédiatement</span>";
                    header("Location: add_collection.php");
                    exit();
                } else {
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $imageTmpName = $_FILES['image']['tmp_name'];
                        $imageName = $_FILES['image']['name'];
                        $imageExtension = pathinfo($imageName, PATHINFO_EXTENSION);
    
                        $validExtensions = ['png', 'jpg', 'jpeg', 'webp', 'avif'];
                        if (in_array(strtolower($imageExtension), $validExtensions)) {
                            $newImageName = uniqid('', true) . '.' . $imageExtension;
                            $uploadDir = '/Jewelre/view/img/collection/';
                            if (!is_dir($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }
                            $imageDestination = $uploadDir . $newImageName;
                            move_uploaded_file($imageTmpName, $imageDestination);
                            $image_chemin = $imageDestination;
                        } else {
                            $_SESSION['erreur'] = '<span>Extension de fichier non autorisée.</span>';
                            header("Location: add_collection.php");
                            exit();
                        }
                    } else {
                        $_SESSION['erreur'] = '<span>Une erreur est survenue avec l\'image.</span>';
                        header("Location: add_collection.php");
                        exit();
                    }
    
                    if(isset($_POST['envente'])) { $sql = "INSERT INTO collection (en_vente, titre, date_sortie, id_fournisseur, image_chemin) VALUES (1, :titre, :date_sortie, :id_vendeur, :image_chemin)"; }
                    else { $sql = "INSERT INTO collection (titre, date_sortie, id_fournisseur, image_chemin) VALUES (:titre, :date_sortie, :id_vendeur, :image_chemin)"; }
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':titre', $titre);
                    $stmt->bindParam(':date_sortie', $date_sortie);
                    $stmt->bindParam(':id_vendeur', $idVendeur);
                    $stmt->bindParam(':image_chemin', $image_chemin);
                    $stmt->execute();
                    
                    $idCollection = $conn->lastInsertId();
    
                    if (!empty($produits)) {
                        $sql = "UPDATE produit SET id_collection = :id_collection WHERE id IN (" . implode(",", array_map('intval', $produits)) . ")";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':id_collection', $idCollection);
                        $stmt->execute();
                    }
    
                    $_SESSION['succes'] = "<span>Collection ajoutée avec succès!</span>";
                    header("Location: add_collection.php");
                    exit();
                }
            }
        }
    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}