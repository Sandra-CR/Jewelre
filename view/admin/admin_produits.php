<?php 
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: admin.php");
    exit();
}

include ('../../model/bdd.php');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);
} catch(PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="shortcut icon" href="../img/logo-site.png">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <title>Produits | Jewelr-e</title>
</head>
<body>
<?php include ('../include/navbar.php') ?>

<?php 
    $allowedTables = ['produit', 'produit_taille', 'produit_suplement', 'produit_image', 'collection'];

    if($tables) {
        foreach($tables as $table) {
            $tableName = $table[0];

            if (!in_array($tableName, $allowedTables)) {
                continue;
            }
    
            echo "<h3>Table " . $tableName . "</h3>";

            $stmt = $conn->query("SELECT * FROM $tableName");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if($rows) {
                echo "<div class='table-container'>";
                echo "<table>";

                echo "<tr>";
                foreach(array_keys($rows[0]) as $column) {
                    echo "<th>" . $column . "</th>";
                }
                if($tableName == "produit_image") {
                    echo "<th id='imageCol'>Image</th>";
                }
                echo "<th class='action'>Actions</th>";
                echo "</tr>";

                foreach($rows as $row) {
                    $columnId = array_keys($rows[0])[0];
                    if($row[$columnId] == 0) { continue; }
                    echo "<tr>";
                    foreach($row as $data) {
                        echo "<td>" . $data . "</td>";
                    }
                    if($tableName == "produit_image") { 
                        $columnIdProduit = array_keys($rows[0])[2];
                        $stmt = $conn->prepare("SELECT * 
                        FROM produit_image pi JOIN produit p ON p.id = pi.id_produit 
                        WHERE pi.id_produit = :id_produit LIMIT 1");
                        $stmt->bindParam(':id_produit', $row[$columnIdProduit]);
                        $stmt->execute();
                        $produit = $stmt->fetch(PDO::FETCH_ASSOC);
                        ?>

                        <td id="image">
                            <img src="<?php echo !empty($produit['image_chemin']) ? htmlspecialchars($produit['image_chemin']) : '../img/boutique/default.png'; ?>" alt="<?php echo htmlspecialchars($produit['type_produit']); ?>">
                        </td>
                    <?php }
                    echo "<td class='action-content'>";
                    echo "<a href='delete.php?table=$tableName&id={$row[$columnId]}' onclick='return confirm(\"Etes-vous sûr de vouloir supprimer cet enregistrement ?\");' class='delete'><i class='bx bx-trash-alt'></i> Supprimer</a>";
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "</div>";
            } else {
                echo "<p>Aucune donnée disponible</p>"; 
            }
            echo "<p><a href='create.php?table=$tableName' class='add'>Ajouter des données</a></p>";
        }
    } else {
        echo "<p>Aucune table trouvée dans la base de données</p>";
    }

    $conn = null;
?>
<br>
<?php include ('../include/footer.php') ?>
</body>
</html>