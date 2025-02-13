<?php
        // Vérification de la soumission du formulaire
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
                $email = htmlspecialchars(trim($_POST['email']));
                $mdp = htmlspecialchars(trim($_POST['mdp']));
                // Requête SQL pour vérifier les informations de connexion
                $stmt = $conn->prepare("SELECT * FROM client WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                if($stmt->rowCount() > 0){
                    $client = $stmt->fetch(PDO::FETCH_ASSOC);
                    // Vérification du mot de passe
                    if ($client && password_verify($mdp, $client['mdp'])) {
                        $_SESSION['pseudo'] = $client['pseudo'];
                        $_SESSION['id'] = $client['id'];
                        header("Location: index.php"); // Redirection vers la page d'accueil
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