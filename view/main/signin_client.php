<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>S'inscrire | Jewelr-e</title>
</head>
<body>
    <!-------------- FORMULAIRE -------------->
    <div class="container" id="container">
        <div class="form-container inscription">
            <form action="signin_client.php" method="POST">
                <!-- Titre -->
                <h1>S'inscrire</h1>
                <!-- Selection du type client/vendeur -->
                <div class="selection-user">
                    <div class="user actif">
                        <a href="signin_client.php">Client</a>
                        <div class="separateur"></div>
                    </div>
                    <div class="user autre">
                        <a href="signin_vendeur.php">Vendeur</a>
                        <div class="separateur"></div>
                    </div>
                </div>
                <?php include ('../../model/inscription_client.php'); ?>
                <!-- Champs du formulaire -->
                <div class="form-content">
                    <div class="input-group">
                        <p><i class='bx bx-user'></i></p>
                        <input type="text" name="pseudo" placeholder="Nom d'utilisateur" required>
                    </div>
                    <div class="input-group">
                        <p><i class='bx bx-envelope'></i></p>
                        <input type="email" name="email" placeholder="Adresse mail" required>
                    </div>
                    <div class="input-group mdp">
                        <p><i class='bx bx-subdirectory-right'></i></p>
                        <input type="password" name="mdp" placeholder="Mot de passe" required>
                    </div>
                    <div class="input-group cfm">
                        <p><i class='bx bx-check'></i></p>
                        <input type="password" name="mdpC" placeholder="Confirmer le mot de passe" required>
                    </div>
                    <!-- Bouton afficher le mdp -->
                    <div class="afficher-mdp">
                        <input type="checkbox">
                        <p>Afficher le mot de passe</p>
                    </div>
                </div>
                <!-- Liens de redirection -->
                <div class="links">
                    <a href="login_client.php">J'ai déjà un compte</a>
                </div>
                <!-- Bouton submit -->
                <div class="btn-submit">
                    <input type="submit" value="S'inscrire">
                </div>
                
            </form>
        </div>
        
    </div>

    
    <!------------ SCRIPT AFFICHER LE MDP ------------>
    <script>
        let input = document.querySelector('.input-group.mdp input');
        let confi = document.querySelector('.input-group.cfm input');
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
</body>
</html>