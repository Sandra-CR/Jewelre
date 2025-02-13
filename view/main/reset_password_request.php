<?php 
session_start();
include ('../../model/bdd.php');
date_default_timezone_set('Europe/Paris');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = htmlspecialchars($_POST['email']);

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $typeUser = isset($_GET['type']) ? $_GET['type'] : null;

        $stmt = $conn->prepare("DELETE FROM password_resets WHERE expires_at < NOW() - INTERVAL 1 MINUTE");
        $stmt->execute();

        if($typeUser == 'client') {
            $stmt = $conn->prepare("SELECT * FROM client WHERE email = :email");
        } else if ($typeUser == 'vendeur') {
            $stmt = $conn->prepare("SELECT * FROM fournisseur WHERE email = :email");
        }

        if (!$typeUser) {
            echo "<p id='erreur'>Le type d'utilisateur n'est pas spécifié</p>";
            exit;
        }

        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+ 15 minutes'));
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at, created_at) VALUES (:email, :token, :expires_at, NOW())");
            $stmt->execute(['email' => $email, 'token' => $token, 'expires_at' => $expiry]);
            
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . "/reset_password.php?token=$token&type=$typeUser";
            $subject = "=?UTF-8?B?" . base64_encode("Réinitialisation de votre mot de passe") . "?=";
            $message = "
            <html>
            <head>
                <title>Réinitialisation de votre mot de passe</title>
            </head>
            <body>
                <p>Bonjour,</p>
                <p>Cliquez sur le lien ci-dessous pour réinitialiser le mot de passe :</p>
                <p><a href='$resetLink'>Réinitialiser le mot de passe</a></p>
                <p>Ce lien expire dans 15 minutes</p>
                <p>Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet e-mail</p>
            </body>
            </html>
            ";

            $headers = "From: no-reply@" . $_SERVER['HTTP_HOST'] . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "Content-Transfer-Encoding: 8bit\r\n";

            if(mail($email, $subject, $message, $headers)) {
                $_SESSION['succes'] = 'Un lien de réinitialisation vous a été envoyé';
                header('Location: reset_password_request.php?type=' . $typeUser);
                exit;
            } else {
                $_SESSION['erreur'] = 'Une erreur s\'est produite lors de l\'envoi du email';
                header('Location: reset_password_request.php?type=' . $typeUser);
                exit;
            }
        } else {
            $_SESSION['erreur'] = 'Aucun compte n\'est joint à cette adresse mail';
            header('Location: reset_password_request.php?type=' . $typeUser);
            exit;
        }
    } catch(PDOException $e) {
        echo "Erreur : " . $e->getMessage();
        exit();
    }
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
    <title>Mot de passe oublié | Jewelr-e</title>
</head>
<body>
    <div class="container" id="container">
        <div class="form-container">
            <h2>Mot de passe oublié</h2>

            <!-- Formulaire de demande de réinitialisation -->
            <form method="POST">
                <div class="selection-user">
                    <!-- Gestion de la requête en fonction du type d'user -->
                    <div class="user <?= ($_GET['type'] == 'client') ? 'actif' : 'autre' ?>">
                        <a href="reset_password_request.php?type=client">Client</a>
                        <div class="separateur"></div>
                    </div>
                    <div class="user <?= ($_GET['type'] == 'vendeur') ? 'actif' : 'autre' ?>">
                        <a href="reset_password_request.php?type=vendeur">Vendeur</a>
                        <div class="separateur"></div>
                    </div>
                </div>
                <!-- Gestion des messages d'erreur et de succes -->
                <?php if (isset($_SESSION['erreur'])) {
                    echo '<p id="erreur">' . $_SESSION['erreur'] . '</p>';
                    unset($_SESSION['erreur']);
                }
                if (isset($_SESSION['succes'])) {
                    echo '<p id="succes">' . $_SESSION['succes'] . '</p>';
                    unset($_SESSION['succes']);
                } ?>
                <div class="form-content">
                    <div class="input-group">
                        <p><i class='bx bx-envelope'></i></p>
                        <input type="email" id="email" name="email" placeholder="Adresse mail" required>
                    </div>
                    <div class="links">
                        <a href="login_client.php" class="forgot-link">Je me souviens de mon mot de passe</a>
                        <a href="signin_client.php">Je n'ai pas encore de compte</a>
                    </div>
                    <div class="btn-submit">
                        <button type="submit">Envoyer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>