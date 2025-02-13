<?php
session_start();
include('bdd.php');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si l'utilisateur est connecté
    if (!isset($_SESSION['id'])) {
        echo "Erreur : L'utilisateur n'est pas connecté.";
        exit;
    }

    // Récupérer l'ID produit depuis la requête POST
    if (isset($_POST['idProduit'])) {
        $idProduit = (int) $_POST['idProduit'];  // Forcer l'idProduit à être un entier
    } else {
        echo "Erreur : ID produit manquant dans la requête.";
        exit;
    }

    // Vérifier si le produit existe dans les favoris
    $idClient = (int) $_SESSION['id'];  // Forcer l'idClient à être un entier
    $stmt = $conn->prepare("SELECT * FROM favoris WHERE id_produit = :idProduit AND id_client = :idClient");
    $stmt->bindParam(':idProduit', $idProduit, PDO::PARAM_INT);
    $stmt->bindParam(':idClient', $idClient, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo "1";  // Le produit est déjà dans les favoris
    } else {
        echo "0";  // Le produit n'est pas dans les favoris
    }
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
