<?php session_start(); ?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Se connecter | Jewelr-e</title>
</head>
<body>
    <!-------------- FORMULAIRE -------------->
    <div class="container" id="container">
        <div class="form-container connexion">
            <!-- Titre -->
            <h1>Se connecter</h1>
            <!-- Selection du type client/vendeur -->
            <div class="selection-user">
                <div class="user actif">
                    <a href="login_client.php">Client</a>
                    <div class="separateur"></div>
                </div>
                <div class="user autre">
                    <a href="login_vendeur.php">Vendeur</a>
                    <div class="separateur"></div>
                </div>
            </div>
            <?php include ('../../model/connexion_client.php'); ?>
            <!-- Champs du formulaire -->
            <form action="login_client.php" method="post">
                <div class="form-content">
                    <div class="input-group">
                        <p><i class='bx bx-envelope'></i></p>
                        <input type="email" id="email" name="email" placeholder="Adresse mail" required>
                    </div>
                    <div class="input-group mdp">
                        <p><i class='bx bx-subdirectory-right'></i></p>
                        <input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required>
                    </div>
                    <!-- Bouton afficher le mdp -->
                    <div class="afficher-mdp">
                        <input type="checkbox">
                        <p>Afficher le mot de passe</p>
                    </div>
                </div>
                <!-- Liens de redirection -->
                <div class="links">
                    <a href="reset_password_request.php?type=client" class="forgot-link">Mot de passe oublié</a>
                    <a href="signin_client.php">Je n'ai pas encore de compte</a>
                </div>
                <!-- Bouton submit -->
                <div class="btn-submit">
                    <input type="submit" value="Se connecter">
                </div>
            </form>
        </div>
    </div>
   
    
    <!------------ SCRIPT AFFICHER LE MDP ------------>
    <script>
        let input = document.querySelector('.input-group.mdp input');
        let checkbox = document.querySelector('.afficher-mdp input');
        checkbox.onclick =function(){
            if(input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        } 
    </script>
</body>
</html>