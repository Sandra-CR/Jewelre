<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Accès admin | Jewelr-e</title>
</head>
<body>
    <!-------------- FORMULAIRE -------------->
    <div class="container" id="container">
        <div class="form-container connexion">
            <form action="admin.php" method="POST">
                <!-- Titre -->
                <h1>Se connecter</h1>
                <!-- Selection du type client/vendeur -->
                <div class="selection-user">
                    <div class="user actif">
                        <a href="admin.php">Admin</a>
                        <div class="separateur"></div>
                    </div>
                </div>

                <?php 
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        include ('../../model/bdd.php');
                        try {
                            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                            $email = htmlspecialchars(trim($_POST['email']));
                            $mdp = htmlspecialchars(trim($_POST['mdp']));
                            $stmt = $conn->prepare("SELECT * FROM administrateur WHERE email = :email");
                            $stmt->bindParam(':email', $email);
                            $stmt->execute();
                            if($stmt->rowCount() > 0){
                                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                                if ($admin && password_verify($mdp, $admin['mdp'])) {
                                    $_SESSION['admin'] = $admin['nom'];
                                    $_SESSION['adminID'] = $admin['id'];
                                    header("Location: admin_dashboard.php"); // Redirection vers la page d'accueil
                                    exit(); // Assure que le script s'arrête ici pour éviter toute exécution supplémentaire
                                } else {
                                    echo "<p><span>Email ou mot de passe incorrect</span></p>";
                                }
                            } else {
                                echo "<p><span>L'adresse mail est introuvable</span></p>";
                            }
                        } catch(PDOException $e) {
                            echo "Erreur : " . $e->getMessage();
                        }
            
                        // Fermeture de la connexion à la base de données
                        $conn = null;
                    }
                ?>

                <!-- Champs du formulaire -->
                <div class="form-content">
                    <div class="input-group">
                        <p><i class='bx bx-envelope'></i></p>
                        <input type="email" name="email" placeholder="Adresse mail" required>
                    </div>
                    <div class="input-group mdp">
                        <p><i class='bx bx-subdirectory-right'></i></p>
                        <input type="password" name="mdp" placeholder="Mot de passe" required>
                    </div>
                    <!-- Bouton afficher le mdp -->
                    <div class="afficher-mdp">
                        <input type="checkbox">
                        <p>Afficher le mot de passe</p>
                    </div>
                </div>
                <!-- Liens de redirection -->
                <div class="links">
                    <a href="#" class="forgot-link">Mot de passe oublié</a>
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