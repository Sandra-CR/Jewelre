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
            <form action="signin_vendeur.php" method="POST">
                <!-- Titre -->
                <h1>S'inscrire</h1>
                <!-- Selection du type client/vendeur -->
                <div class="selection-user">
                    <div class="user autre">
                        <a href="signin_client.php">Client</a>
                        <div class="separateur"></div>
                    </div>
                    <div class="user actif">
                        <a href="signin_vendeur.php">Vendeur</a>
                        <div class="separateur"></div>
                    </div>
                </div>
                <?php include ('../../model/inscription_vendeur.php'); ?>
                <!-- Champs du formulaire -->
                <div class="form-content">
                    <div class="input-group">
                        <p><i class='bx bx-building' ></i></p>
                        <input type="text" name="nomE" placeholder="Nom d'entreprise" required>
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
                    <a href="login_vendeur.php">J'ai déjà un compte</a>
                </div>
                <!-- Bouton submit -->
                <div class="btn-submit">
                    <input type="submit" value="S'inscrire">
                </div>
            </form>
        </div>
    </div>

    <!-------------- CONDITIONS DE MOT DE PASSE -------------->
    <div class="password-check">
        <h3>Le mot de passe doit...</h2>
        <div class="item char">
            <!-- <i class='bx bxs-check-circle'></i> Pour le rendre OK -->
            <p class="check char"><i class='bx bxs-x-circle'></i></p>
            <p>Contenir au moins 8 caractères</p>
        </div>
        <div class="item maj">
            <p class="check maj"><i class='bx bxs-x-circle'></i></p>
            <p>Contenir au moins 1 majuscule</p>
        </div>
        <div class="item spe">
            <p class="check spe"><i class='bx bxs-x-circle'></i></p>
            <p>Contenir au moins 1 caractère spécial</p>
        </div>
    </div>
    
    
    <!------------ SCRIPT AFFICHER LE MDP ------------>
    <script>
        let input = document.querySelector('.input-group.mdp input');
        let confi = document.querySelector('.input-group.cfm input');
        let checkbox = document.querySelector('.afficher-mdp input');

        const passwordCheck = document.querySelector('.password-check');
        const char = document.querySelector(".char");
        const checkChar = document.querySelector('.check.char');
        const maj = document.querySelector(".maj");
        const checkMaj = document.querySelector('.check.maj');
        const spe = document.querySelector(".spe");
        const checkSpe = document.querySelector('.check.spe');


        checkbox.onclick = function() {
            if(input.type === "password") {
                input.type = "text";
                confi.type = "text";
            } else {
                input.type = "password";
                confi.type = "password";
            }
        };

        passwordCheck.style.display = 'none';
        input.addEventListener('focus', () => {
            passwordCheck.style.display = 'block';
        });
        input.addEventListener('blur', () => {
                passwordCheck.style.display = 'none';
        });

        input.addEventListener('input', () => {
            const pass = input.value;

            if(pass.length >= 8) {
                char.classList.add('active');
                checkChar.innerHTML = '<i class=\'bx bxs-check-circle\'></i>';
            } else {
                char.classList.remove('active');
                checkChar.innerHTML = '<i class=\'bx bxs-x-circle\'></i>';
            }

            if(/[A-Z]/.test(pass)) {
                maj.classList.add('active');
                checkMaj.innerHTML = '<i class=\'bx bxs-check-circle\'></i>';
            } else {
                maj.classList.remove('active');
                checkMaj.innerHTML = '<i class=\'bx bxs-x-circle\'></i>';
            }

            if(/[^A-Za-z0-9]/.test(pass)) {
                spe.classList.add('active');
                checkSpe.innerHTML = '<i class=\'bx bxs-check-circle\'></i>';
            } else {
                spe.classList.remove('active');
                checkSpe.innerHTML = '<i class=\'bx bxs-x-circle\'></i>';
            }
        });


    </script>
</body>
</html>