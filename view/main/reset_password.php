<?php
session_start();

// Soumission du form
if($_SERVER['REQUEST_METHOD'] == "POST") {
    include ('../../model/bdd.php');

    // Récupération des données
    $token = htmlspecialchars($_POST['token']);
    $newPassword = trim($_POST['mdp']);
    $confirmPassword = trim($_POST['mdpC']);
    $typeUser = $_GET['type']; // client ou vendeur

    // Filtre de vérification
    if($newPassword != $confirmPassword) {
        echo "<p id='erreur'>Les deux mots de passe ne sont pas identiques</p>";
        $_SESSION['erreur'] = 'Les deux mots de passe ne sont pas identiques';
        header('Location: reset_password.php?token=' . $token . '&type=' . $typeUser);
        exit;
    }
    // Filtre de longueur
    if(strlen($newPassword) < 8) {
        $_SESSION['erreur'] = 'Le mot de passe doit contenir minimum 8 caractères';
        header('Location: reset_password.php?token=' . $token . '&type=' . $typeUser);
        exit;
    }

    // Connexion
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // On recherche l'entrée dans la BDD
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = :token AND expires_at > NOW()");
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        // Traitement si résultat
        if($stmt->rowCount() > 0) {
            $email = $stmt->fetchColumn();
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Vérification de l'user pour la requête
            if($typeUser == 'client') {
                $stmt = $conn->prepare("UPDATE client SET mdp = :mdp WHERE email = :email");
            } else if ($typeUser == 'vendeur') {
                $stmt = $conn->prepare("UPDATE fournisseur SET mdp = :mdp WHERE email = :email");
            }
            $stmt->bindParam(':mdp', $hashedPassword);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            // Suppression du token
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = :token");
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            // Redirection en fonction de l'user
            if($typeUser == 'client') {
                header('Location: login_client.php');
            } else if ($typeUser == 'vendeur') {
                header('Location: login_vendeur.php');
            }
            exit;
            
        } else { // Si aucune entrée n'est trouvée dans la BDD
            echo "<p id='error'>Le lien de réinitialisation est invalide ou expiré</p>";
        }

    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        exit();
    }

} else if(isset($_GET['token'])) { // Sécurisation du token 
    $token = htmlspecialchars($_GET['token']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Nouveau mot de passe | Jewelr-e</title>
</head>
<body>
<div class="container" id="container">
        <div class="form-container">
            <h2>Nouveau mot de passe</h2>
            <form action="" method="POST">
                <!-- Conservation du token en hidden -->
                <input type="hidden" name="token" value="<?= htmlspecialchars($token)?>">

                <div class="selection-user">
                    <!-- Gestion de la requête en fonction du type d'user -->
                    <?php if($_GET['type'] == 'client') {?>
                        <div class="user actif">
                            <a href="reset_password.php?token= <?php echo $token; ?> &type= <?php echo $typeUser; ?>">Client</a>
                            <div class="separateur"></div>
                        </div>
                    <?php } else if($_GET['type'] == 'vendeur') { ?>
                        <div class="user actif">
                            <a href="#">Vendeur</a>
                            <div class="separateur"></div>
                        </div>
                    <?php } ?>
                </div>

                <!-- Gestion des messages d'erreur et de succes -->
                <?php if (isset($_SESSION['erreur'])) {
                    echo '<p id="erreur">' . $_SESSION['erreur'] . '</p>';
                    unset($_SESSION['erreur']);
                } ?> 

                <!-- Contenu du formulaire -->
                <div class="form-content">
                    <div class="input-group mdp"> 
                        <p><i class='bx bx-subdirectory-right'></i></p>
                        <input type="password" id="mdp" name="mdp" placeholder="Mot de passe" required>
                    </div>
                    <div class="input-group cfm">
                        <p><i class='bx bx-check'></i></p>
                        <input type="password" name="mdpC" placeholder="Confirmer le mot de passe" required>
                    </div>
                    <div class="afficher-mdp">
                        <input type="checkbox">
                        <p>Afficher le mot de passe</p>
                    </div>
                    <div class="btn-submit">
                        <button type="submit">Réinitialiser</button>
                    </div>
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