<?php session_start(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Jewelr-e | Accueil</title>
</head>
<body id="main">
    <!------------------------ NAVBAR ------------------------>
    <?php include ('../include/navbar.php');?>

    <!------------------------ CAROUSEL ------------------------>
    <div class="slider">
        <div class="slides">
            <!-- <a href=""><img class="slide" src="../img/carousel/slider2.png" alt="img1"></a> -->
            <a href="http://localhost/Jewelre/view/boutique/collection_page.php?id=3"><img class="slide" src="../img/carousel/slider1.png" alt="img1"></a>
            <a href=""><img class="slide" src="../img/carousel/slider2.png" alt="img2"></a>
        </div>
        <button class="prev" onclick="plusSlides(-1)"><i class='bx bx-chevron-left bx-lg'></i></button>
        <button class="next" onclick="plusSlides(1)"><i class='bx bx-chevron-right bx-lg'></i></button>
        <div style="text-align:center" class="dots">
            <span class="dot" onclick="currentSlide(0)"></span>
            <span class="dot" onclick="currentSlide(1)"></span>
        </div>
    </div>

    <!------------------------ PANNEAU CHOIX ------------------------>
    <div class="choice-panel">
        <div class="top-panel">
            <h2>Trouvez votre bonheur</h2>
            <div class="line"></div>
        </div>
        <div class="image-panel">
            <a href="../boutique/catalogue.php?filtre_bague=on">
                <figure>
                    <h4>Bagues</h4>
                    <img src="../img/choice/bague.png" alt="image bague">
                </figure>
            </a>
            <a href="../boutique/catalogue.php?filtre_earrings=on">
                <figure>
                    <h4>Boucles d'oreilles</h4>
                    <img src="../img/choice/boucles.png" alt="image boucles d'oreille">
                </figure>
            </a>
            <a href="../boutique/catalogue.php?filtre_bracelet=on">
                <figure>
                    <h4>Bracelets</h4>
                    <img src="../img/choice/bracelet.png" alt="image bracelet">
                </figure>
            </a>
            <a href="../boutique/catalogue.php?filtre_collier=on">
                <figure>
                    <h4>Colliers</h4>
                    <img src="../img/choice/collier.png" alt="image collier">
                </figure>  
            </a>
            <a href="../boutique/collection.php">
                <figure>
                    <h4>Collections</h4>
                    <img src="../img/choice/collection.png" alt="image collection">
                </figure>
            </a>
        </div>
    </div>

    <!------------------------ LIAISONS JS ------------------------>
    <div class="grid">
        <div class="coll-image">
            <div class="image-content">
                <img src="../img/collection/floral-chic.png" alt="Collier de la collection Floral Chic">
            </div>
        </div>
        <div class="coll-info">
            <div class="info-content">
                <h2>Collection Floral Chic</h2>
                <p>Découvrez la nouvelle collection Floral Chic de ce printemps 2024, parfaite pour les beaux jours qui approchent à grand pas.</p>
                <a href="#">Découvrir</a>
            </div>
        </div>
        <div class="coll-slider">
            <div class="slider-content">
                <div class="slides2">
                    <div class="card">
                        <!-- <a href="#"><img src="BD RELATED (image article)" alt="Image de l'article"></a> -->
                        <div class="card-body">
                            <div class="card-titre">
                                <!-- <a href="#">BD RELATED (titre composé)</a> -->
                                <p><i class='bx bx-heart'></i></p>
                                <!-- !!! Faire en sorte de changer en coeur plein quand favori !!! -->
                            </div>
                            <!-- <h5>BD RELATED (prix)</h5> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!------------------------ FOOTER ------------------------>
    <?php include ('../include/footer.php');?>

    <!------------------------ LIAISONS JS ------------------------>
    <script src="../js/slider_accueil.js"></script>
</body>
</html>