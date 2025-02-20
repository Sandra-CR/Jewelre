<?php 
session_start();
if (!isset($_SESSION['entreprise']) && !isset($_SESSION['pseudo'])) {
    header("Location: login_client.php");
    exit();
}

include ('../../model/bdd.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
} 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/contact.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Nous contacter | Jewelr-e</title>
</head>
<body>

<div class="retour">
    <button onclick="history.back();" id="btnRetour">< Retour</button>
</div>

<div class="contact" id="contact">
        <!-- titres -->
        <h5>Me contacter</h5>
        <h4>Formulaire de contact</h4>

        <!-- formulaire -->
        <div class="form-container">
            <form action="https://formsubmit.co/sandracr2005@gmail.com" target="_blank" method="POST">
                <div class="form-grp">
                    <label for="name">Nom</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-grp">
                    <label for="email">Email</label>
                    <input type="email" name="email" value="<?php echo isset($user['email']) && !empty($user['email']) ? htmlspecialchars($user['email']) : ''; ?>" required>
                </div>
                <div class="form-grp">
                    <label for="message">Message</label>
                    <textarea name="message" id="message" rows="9" required></textarea>
                </div>
                <div class="btn">
                    <button type="submit">Envoyer</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>