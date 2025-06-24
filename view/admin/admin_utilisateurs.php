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
    <title>Utilisateurs | Jewelr-e</title>
</head>
<body>
<?php include ('../include/navbar.php') ?>

<?php 
    if($_SESSION['adminID'] === 0) { $allowedTables = ['client', 'administrateur', 'fournisseur']; }
    else { $allowedTables = ['client', 'fournisseur']; }

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
                echo "<th class='action'>Actions</th>";
                echo "</tr>";

                foreach($rows as $row) {
                    $columnId = array_keys($rows[0])[0];
                    if($row[$columnId] == 0) { continue; }
                    echo "<tr>";
                    foreach($row as $data) {
                        echo "<td>" . $data . "</td>";
                    }
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