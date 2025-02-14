<?php
require(__DIR__ . '/tfpdf/tfpdf.php');

include ('bdd.php');
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
    exit();
}

$idCommande = $_POST['idCommande'];
$stmt = $conn->prepare("SELECT * FROM commande WHERE id = :id");
$stmt->bindParam(':id', $idCommande);
$stmt->execute();
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

list($dateEn, $heureFull) = explode(" ", $commande['date_commande']);
  $dateFr = DateTime::createFromFormat('Y-m-d', $dateEn)->format('d/m/Y');
  $heureFormat = date("H:i", strtotime($heureFull));

$stmt = $conn->prepare("SELECT * FROM commande_contenu WHERE id_commande = :id_commande");
$stmt->bindParam(':id_commande', $idCommande);
$stmt->execute();
$produits = $stmt->fetchALl(PDO::FETCH_ASSOC);

$idClient = $commande['id_client'];
$stmt = $conn->prepare("SELECT nom, prenom, pseudo, email FROM client WHERE id = :id");
$stmt->bindParam(':id', $idClient);
$stmt->execute();
$client = $stmt->fetch(PDO::FETCH_ASSOC);


Class PDF extends TFPDF {
    // Pied de page
    function Footer() {
        $this->setY(-15);
        $this->SetFont('DejaVu', 'I', 10);
        $this->Cell(0, 10, 'Jewelr-e', 0, 0, 'C');
    }

    // Haut de table
    function TableHeader() {
        $width = $this->GetPageWidth();

        $this->SetFont('DejaVu', 'B', 12);
        $this->SetFillColor(174, 149, 118);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(255, 255, 255);
        $this->Cell(($width / 5 * 3.25), 9, 'Nom du produit', 1, 0, 'C', true);
        $this->Cell(($width / 5) - 10, 9, 'Vendeur', 1, 0, 'C', true);
        $this->Cell(($width / 5 * 0.75) - 10, 9, 'Prix (€)', 1, 1, 'C', true);
    }

    // Contenu de table
    function TableRow($produit, $vendeur, $prix, $isOdd) {
        $width = $this->GetPageWidth();

        if ($isOdd) { $this->SetFillColor(230, 230, 230); }
        else { $this->SetFillColor(255, 255, 255); }

        $this->SetFont('DejaVu', '', 9.5);
        $this->SetTextColor(000, 000, 000);
        $this->Cell(($width / 5 * 3.25), 10, $produit, 0, 0, '', true);
        $this->Cell(($width / 5) - 10, 10, $vendeur, 0, 0, 'C', true);
        $this->SetFont('DejaVu', 'B', 9.5);
        $this->SetTextColor(138, 117, 91);
        $this->Cell(($width / 5 * 0.75) - 10, 10, $prix, 0, 1, 'R', true);
    }

    function TableTotal($prixTotal) {
        $width = $this->GetPageWidth();
        $this->Cell(($width / 6 * 4) - 20, 10, '', 0);

        $this->SetFont('DejaVu', 'B', 12);
        $this->SetFillColor(174, 149, 118);
        $this->SetTextColor(255, 255, 255);
        $this->SetDrawColor(174, 149, 118);
        $this->Cell(($width / 6), 10, 'Total TTC', 1, 0, 'C', true);

        $this->SetFontSize(11);
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(174, 149, 118);
        $this->Cell(($width / 6), 10, $prixTotal.'€', 1, 1, 'C');
    }
}

ob_start();
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    function getPostData($key) {
        return isset($_POST[$key]) ? mb_convert_encoding($_POST[$key], 'UTF-8', 'auto') : '';
    }
    $idCommande = getPostdata('idCommande');
    
    $pdf = new PDF();
    $pdf->Addpage();
    $pdf->SetTitle('Jewelr-e | Reçu de commande N°'.$idCommande, true);
    $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
    $pdf->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
    $pdf->AddFont('DejaVu', 'I', 'DejaVuSansCondensed-Oblique.ttf', true);

    if(file_exists('../view/img/logo-site.png')) { $pdf->image('../view/img/logo-site.png', 12, 10, 30); }
    $pdf->SetFont('DejaVu', 'B', 16);
    $pdf->Cell(0, 6, 'Commande N°'. $idCommande, 0, 1, 'C');
    
    $pdf->SetFont('DejaVu', 'I', 11);
    $pdf->SetTextColor(100, 100, 100);
    $pdf->Cell(0, 10, $dateFr.' à '.$heureFormat, 0, 1, 'C');
    $pdf->Ln(20);


    // Infos des deux partis
    $width = $pdf->GetPageWidth();

    $pdf->SetFont('DejaVu', 'B', 12);
    $pdf->SetTextColor(138, 117, 91);
    $pdf->Cell(40, 10, 'Vendeur');

    $pdf->SetX($width / 2 + 5);
    $pdf->Cell(40, 10, 'Acheteur', 0, 1);
    $pdf->Ln(-3);

    $pdf->SetFont('DejaVu', '', 11);
    $pdf->SetTextColor(000, 000, 000);
    $pdf->Cell(12, 10, 'Nom : ');
    $pdf->Cell(0, 10, 'Jewelr-e');

    $pdf->SetX($width / 2 + 5);
    $pdf->Cell(12, 10, 'Nom : ');
    $pdf->Cell(0, 10, $client['nom'], 0, 1);
    $pdf->Ln(-4);

    $pdf->Cell(12, 10, 'Pays : ');
    $pdf->Cell(0, 10, 'FRANCE');

    $pdf->SetX($width / 2 + 5);
    $pdf->Cell(17, 10, 'Prénom : ');
    $pdf->Cell(0, 10, $client['prenom'], 0, 1);
    $pdf->Ln(-4);

    $pdf->Cell(35, 10, 'Numéro de SIREN : ');
    $pdf->Cell(0, 10, '362 521 879');

    $pdf->SetX($width / 2 + 5);
    $pdf->Cell(34, 10, 'Nom d\'utilisateur : ');
    $pdf->Cell(0, 10, $client['pseudo'], 0, 1);
    $pdf->Ln(-4);

    $pdf->Cell(42, 10, 'Numéro de téléphone : ');
    $pdf->Cell(0, 10, '06 43 37 08 33', 0, 0);

    $pdf->SetX($width / 2 + 5);
    $pdf->Cell(26, 10, 'Adresse mail : ');
    $pdf->Cell(0, 10, $client['email'], 0, 1);

    $pdf->Ln(10);

    $pdf->TableHeader();

    $ligne = 1;
    foreach($produits as $produit) {
        $idProduit = $produit['id_produit'];
        
        $stmt = $conn->prepare("SELECT p.id, p.type_produit, p.motif, p.matiere_p, p.couleur_p, p.prix, p.matiere_s, p.couleur_s, p.id_fournisseur, c.titre, f.entreprise, GROUP_CONCAT(CONCAT(ps.matiere, ' ', ps.couleur) ORDER BY ps.id ASC SEPARATOR ' et ') AS pierres_info, (SELECT pi.image_chemin FROM produit_image pi WHERE pi.id_produit = p.id LIMIT 1) AS image_chemin FROM produit p LEFT JOIN produit_suplement ps ON ps.id_produit = p.id LEFT JOIN fournisseur f ON f.id = p.id_fournisseur LEFT JOIN collection c ON c.id = p.id_collection WHERE p.id = :id");
        $stmt->bindParam(':id', $idProduit);
        $stmt->execute();
        $produitSpe = $stmt->fetch(PDO::FETCH_ASSOC);

        $pierresInfo = $produitSpe['pierres_info'];
        $titreProduit = $produitSpe['type_produit'] 
        . (!empty($produitSpe['motif']) ? " " . $produitSpe['motif'] : "") 
        . " " . $produitSpe['matiere_p'] . " " . $produitSpe['couleur_p'] 
        . (!empty($produitSpe['matiere_s']) ? " " . $produitSpe['matiere_s'] : "") 
        . (!empty($produitSpe['couleur_s']) ? " " . $produitSpe['couleur_s'] : "") 
        . (!empty($pierresInfo) ? " " . $pierresInfo : "");

        $prix = $produitSpe['prix'];
        $vendeur = $produitSpe['entreprise'];

        $pdf->TableRow($titreProduit, $vendeur, $prix, $ligne % 2 === 0);
        $ligne++;
    }
    $pdf->Ln(10);

    $pdf->TableTotal($commande['prix_total']);

    $pdf->Output('', 'commande-'.$idCommande.'-jewelre.pdf');
}