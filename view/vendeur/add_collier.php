<?php 
session_start();
if (!isset($_SESSION['entreprise'])) {
    header("Location: login_vendeur.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/add_article.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <title>Ajouter des boucles d'oreilles | Jewelr-e</title>
</head>
<body>
    <!------------------------ NAVBAR ------------------------>
    <?php include ('../include/navbar.php');?>

    <!------------------------ FORMULAIRE ------------------------>
    <div class="page">
        <div class="categorie">
            <div class="btns">
                <a href="add_bague.php" class="categorie-btn">Bagues</a>
                <a href="add_earrings.php" class="categorie-btn">Boucles d'oreilles</a>
                <a href="add_bracelet.php" class="categorie-btn">Bracelets</a>
                <a href="add_collier.php" class="categorie-btn active">Colliers</a>
                <a href="#" class="categorie-btn">Collection</a>
            </div>
            <p>*Champs obligatoires</p>
        </div>
        <div class="formulaire">
            <form action="add_collier.php" method="POST" enctype="multipart/form-data">
                <?php include ('../../model/ajouter_collier.php'); ?>
                <div class="champs">
                    <div class="caracteristiques">
                        <h3>Caractéristiques</h3>
                        <div class="input-group">
                            <label for="genre">Genre*</label> 
                            <select name="genre">
                                <option value="1" default>Femme</option>
                                <option value="2">Homme</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label for="matierep">Matière principale*</label>
                            <input type="text" name="matierep">
                        </div>
                        <div class="input-group">
                            <label for="couleurp">Couleur principale*</label>
                            <input type="text" name="couleurp">
                        </div>
                        <div class="input-group">
                            <label for="matierep">Matière secondaire</label>
                            <input type="text" name="matieres">
                        </div>
                        <div class="input-group">
                            <label for="couleurp">Couleur secondaire</label>
                            <input type="text" name="couleurs">
                        </div>
                        <div class="input-group">
                            <label for="titrage">Titrage</label>
                            <input type="number" name="titrage" min="0" max="1000" step="1">
                        </div>
                        <div class="input-group">
                            <label for="chaine">Chaine</label>
                            <input type="text" name="chaine">
                        </div>
                        <div class="input-group">
                            <label for="fermoir">Fermoir</label>
                            <input type="text" name="fermoir">
                        </div>
                        <div class="input-group">
                            <label for="prix">Prix (€)*</label>
                            <input type="number" name="prix" min="0" step=".01">
                        </div>
                        <div class="input-group file">
                            <label for="images">Images*</label>
                            <input type="file" name="images[]" accept=".png, .jpg, .jpeg, .webp" multiple>
                        </div>
                    </div>
                    <div class="pierres">
                        <h3>Détails</h3>
                        <div class="check principale">
                            <input type="checkbox" id="checkp" name="checkp">
                            <p>Pendentif</p>
                        </div>
                        <div class="pierre principale" id="pierrep">
                            <div class="input-group">
                                <label for="pmatiere">Matière*</label>
                                <input type="text" name="pmatiere">
                            </div>
                            <div class="input-group">
                                <label for="pcouleur">Couleur*</label>
                                <input type="text" name="pcouleur">
                            </div>
                            <div class="input-group">
                                <label for="pforme">Forme</label>
                                <input type="text" name="pforme">
                            </div>
                            <div class="input-group">
                                <label for="pcaratage">Caratage</label>
                                <input type="number" name="pcaratage" min="0" step=".0001">
                            </div>
                        </div>
                        <div class="check secondaire">
                            <input type="checkbox" id="checks" name="checks">
                            <p>Pierre</p>
                        </div>
                        <div class="pierre secondaire" id="pierres">
                            <div class="input-group">
                                <label for="smatiere">Matière*</label>
                                <input type="text" name="smatiere">
                            </div>
                            <div class="input-group">
                                <label for="scouleur">Couleur*</label>
                                <input type="text" name="scouleur">
                            </div>
                            <div class="input-group">
                                <label for="snombre">Nombre*</label>
                                <input type="number" name="snombre" min="1" step="1">
                            </div>
                            <div class="input-group">
                                <label for="scaratage">Caratage</label>
                                <input type="number" name="scaratage" min="0" step=".0001">
                            </div>
                            <div class="input-group">
                                <label for="ssertis">Sertis*</label>
                                <input type="text" name="ssertis">
                            </div>
                        </div>
                    </div>
                    <div class="tailles-stock">
                        <h3>Stock</h3>
                        <div class="input-group">
                            <label for="qt1">Quantité dispo</label>
                            <input type="number" name="qt1" min="0" step="1">
                        </div>
                        <div class="input-group">
                            <label for="longueur">Longueur (cm)</label>
                            <input type="number" name="longueur" min="0" step=".01">
                        </div>
                        <div class="input-group">
                            <label for="largeur">Largeur (cm)</label>
                            <input type="number" name="largeur" min="0" step=".01">
                        </div>
                        <div class="input-group">
                            <label for="poids">Poids (gr)</label> 
                            <input type="number" name="poids" min="0" step=".01">
                        </div>
                        <p id="info">INFO: Il est nécessaire d'indiquer la quantité de stock si vous souhaitez mettre en vente le produit immédiatement.
                            Le produit peut être mis en vente ultérieurement.
                        </p>
                        <div class="conditions">
                            <div class="condi">
                                <input type="checkbox" name="envente">
                                <p>Je souhaite mettre en vente cet article</p>
                            </div>
                            <div class="condi">
                                <input type="checkbox" name="engagement">
                                <p>Je m'engage à fournir un article conforme aux critères ici présents*</p>
                            </div>
                        </div>
                        <div class="btn-submit">
                            <input type="submit" value="AJOUTER">
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
    </div>
    
    <!------------------------ FOOTER ------------------------>
    <?php include ('../include/footer.php');?>
