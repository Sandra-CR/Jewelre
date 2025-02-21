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
        $nomE = htmlspecialchars(trim($_POST['nomE']));
        $email = htmlspecialchars(trim($_POST['email']));
        $mdp = htmlspecialchars(trim($_POST['mdp']));
        $mdpC = htmlspecialchars(trim($_POST['mdpC']));

        $stmt = $conn->prepare("SELECT * FROM fournisseur WHERE entreprise = :nomE");
        $stmt->bindParam(':nomE', $nomE);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo "<p><span>Cette entreprise est déjà inscrite</span></p>";
        } else {
            $stmt = $conn->prepare("SELECT * FROM fournisseur WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                echo "<p><span>Cette adresse mail est déjà utilisée</span></p>";      
            } else {
                if(strlen($mdp) < 8 || !preg_match("/[A-Z]/", $mdp) || !preg_match("/[^A-Za-z0-9]/", $mdp)) {
                    echo "<p><span>Les conditions de mot de passe n'ont pas été respectées</span></p>";
                } else {
                    if($mdp != $mdpC) {
                        echo "<p><span>Veillez à ce que les deux mots de passe soient identiques</span></p>";
                    } else {
                        // Hacher le mot de passe
                        $mdp_hash = password_hash($mdp, PASSWORD_DEFAULT);
    
                        // Préparation de la requête SQL
                        $stmt = $conn->prepare("INSERT INTO fournisseur (entreprise, email, mdp) VALUES
                        (:nomE, :email, :mdp)");
    
                        // Liaison des paramètres
                        $stmt->bindParam(':nomE', $nomE);
                        $stmt->bindParam(':email', $email);
                        $stmt->bindParam(':mdp', $mdp_hash); // Utilisation du mot de passe haché
    
                        // Exécution de la requête
                        $stmt->execute();
    
                        // Redirection vers la page connexion.php
                        header("Location: login_vendeur.php");
                        exit(); // Assure que le script s'arrête ici pour éviter toute exécution supplémentaire
                    }
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