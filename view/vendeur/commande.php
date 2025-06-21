<?php 
session_start();
if (!isset($_SESSION['entreprise'])) {
    header("Location: /Jewelre/view/main/login_vendeur.php");
    exit();
}

include ('../../model/bdd.php');

$idVendeur = $_SESSION['id'];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

$stmt = $conn->prepare("SELECT id FROM produit WHERE id_fournisseur = :id_fournisseur");
$stmt->bindParam(':id_fournisseur', $idVendeur);
$stmt->execute();
$mesProduits = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (!empty($mesProduits)) {
    $placeholders = implode(',', array_fill(0, count($mesProduits), '?'));
    $stmt = $conn->prepare("SELECT * FROM commande_contenu WHERE id_produit IN ($placeholders)");
    $stmt->execute($mesProduits);
    $commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $groupedCommandes = [];
    foreach ($commandes as $commande) {
        $idCommande = $commande['id_commande'];
        if (!isset($groupedCommandes[$idCommande])) {
            $groupedCommandes[$idCommande] = [];
        }
        $groupedCommandes[$idCommande][] = $commande;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/commande.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <title>Mes commandes | Jewelr-e</title>
</head>
<body>
    <!-- Navbar -->
    <?php include ('../include/navbar.php'); ?>
    
    <?php krsort($groupedCommandes); ?>
    <div class="page">
        <div class="panel">
            <p id="panelTitle">Commandes (<?php echo count($groupedCommandes); ?>)</p>
            <?php foreach ($groupedCommandes as $idCommande => $produits) {
                echo '<button type="button" class="commande-btn" data-id="'. $idCommande .'">N°'. $idCommande .'</button>';
            } ?>
        </div>
        
        <div class="commandes">
            <?php 
            $first = true;
            foreach ($groupedCommandes as $idCommande => $produits) { 
                $stmt = $conn->prepare("SELECT * FROM commande WHERE id = :id");
                $stmt->bindParam(':id', $idCommande);
                $stmt->execute();
                $infosCommande = $stmt->fetch(PDO::FETCH_ASSOC);

                list($date, $heure) = explode(' ', $infosCommande['date_commande']);
                $dateFr = date('d/m/Y', strtotime($date));

                $stmt = $conn->prepare("SELECT * FROM client WHERE id = :id");
                $stmt->bindParam(':id', $infosCommande['id_client']);
                $stmt->execute();
                $client = $stmt->fetch(PDO::FETCH_ASSOC);

                $stmt = $conn->prepare("SELECT * FROM adresse WHERE id = :id");
                $stmt->bindParam(':id', $infosCommande['id_adresse']);
                $stmt->execute();
                $adresse = $stmt->fetch(PDO::FETCH_ASSOC);
                
                ?>

                <div class="commande" id="commande-<?php echo $idCommande; ?>" style="display: <?php echo $first ? 'block' : 'none'; ?>;">
                    <div class="titre">
                        <div class="client">
                            <h3>Acheteur</h3>
                            <div class="group">
                                <span>Nom</span><p><?php echo $client['nom']; ?></p>
                            </div>
                            <div class="group">
                                <span>Prénom</span><p><?php echo $client['prenom']; ?></p>
                            </div>
                            <div class="group">
                                <span>Adresse mail</span><p><?php echo $client['email']; ?></p>
                            </div>
                        </div>
                        <div class="info-commande">
                            <h3>Informations</h3>
                            <div class="group">
                                <span>Date de commande</span><p><?php echo $dateFr; ?></p>
                            </div>
                            <div class="group">
                                <span>Adresse</span><p><?php echo $adresse['numero'] ." ". $adresse['rue'] .", ". $adresse['ville']; ?></p>
                            </div>
                            <div class="group pays">
                                <span>Pays</span><p><?php echo $adresse['pays']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="content">
                        <?php 
                        $prixTotal = 0;
                        foreach ($produits as $produit) { 
                            $prixTotal += $produit['prix_achat'];
                            $stmt = $conn->prepare("SELECT image_chemin FROM produit_image WHERE id_produit = :id_produit LIMIT 1");
                            $stmt->bindParam(':id_produit', $produit['id_produit']);
                            $stmt->execute();
                            $image = $stmt->fetch(PDO::FETCH_ASSOC);
                            $imageUrl = $image ? $image['image_chemin'] : '../img/boutique/default.jpg';
                            ?>
                            <div class="item">
                                <div class="left">
                                    <img src="<?php echo $image['image_chemin']; ?>" alt="Image de l'article">
                                    <h5><?php echo $produit['nom_produit']; ?></p>
                                </div>
                                <p><?php echo $produit['prix_achat'] ."€"; ?></p>
                            </div>
                        <?php } ?>

                        <div class="total">
                            <p>Total TTC</p>
                            <h4><?php echo $prixTotal ."€"; ?></h4>
                        </div>
                    </div>
                </div>
            <?php 
            $first = false;
            } ?>
        </div>
    </div>

    <!-- Footer -->
    <?php include ('../include/footer.php'); ?>


    <script>
    const buttons = document.querySelectorAll('.commande-btn');
    const commandes = document.querySelectorAll('.commande');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            commandes.forEach(cmd => {
                cmd.style.display = 'none';
            });

            const commandeToShow = document.getElementById('commande-' + id);
            if (commandeToShow) {
                commandeToShow.style.display = 'block';
            }
        });
    });

    if (buttons.length > 0) {
        buttons[0].classList.add('active');
        commandes.forEach(cmd => cmd.style.display = 'none');
        const firstCommande = document.getElementById('commande-' + buttons[0].getAttribute('data-id'));
        if (firstCommande) firstCommande.style.display = 'block';
    }

    </script>
</body>
</html>