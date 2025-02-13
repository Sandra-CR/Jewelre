<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Connexion à la base de données
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "jewelre";

        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
            // Configuration de PDO pour générer une exception en cas d'erreur
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Récupération des données du formulaire
            $pseudo = htmlspecialchars(trim($_POST['pseudo']));
            $email = htmlspecialchars(trim($_POST['email']));
            $mdp = htmlspecialchars(trim($_POST['mdp']));
            $mdpC = htmlspecialchars(trim($_POST['mdpC']));

            $stmt = $conn->prepare("SELECT * FROM client WHERE pseudo = :pseudo");
            $stmt->bindParam(':pseudo', $pseudo);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo "<p><span>Ce nom d'utilisateur est déjà pris</span></p>";
            } else {
                $stmt = $conn->prepare("SELECT * FROM client WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    echo "<p><span>Cette adresse mail est déjà utilisée</span></p>";
                } else {
                    if($mdp === $mdpC) {
                        // Hacher le mot de passe
                        $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);

                        // Préparation de la requête SQL
                        $stmt = $conn->prepare("INSERT INTO client (pseudo, email, mdp) VALUES
                        (:pseudo, :email, :mdp)");
                        $stmt->bindParam(':pseudo', $pseudo);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':mdp', $mdp_hash);
                        $stmt->execute();
                        // Redirection vers la page connexion.php
                        header("Location: login_client.php");
                        exit(); // Assure que le script s'arrête ici pour éviter toute exécution supplémentaire
                    } else {
                        echo "<p><span>Veillez à ce que les deux mots de passe soient identiques</span></p>";
                    }
                }
            }

        } catch(PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }

        // Fermeture de la connexion à la base de données
        $conn = null;
    }
?>