<?php
require("includes/conn.inc.php");

$sql_kat = "SELECT * FROM tbl_kategorien";
$result_kat = $conn->query($sql_kat) or die("Fehler in der Query: " . $conn->error);

$sql_all = "SELECT * FROM tbl_allergene";
$result_all = $conn->query($sql_all) or die("Fehler in der Query: " . $conn->error);

$sql_einheit = "SELECT * FROM tbl_einheiten";
$result_einheit = $conn->query($sql_einheit) or die("Fehler in der Query: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <title>Speisekarte</title>
</head>

<body>
    <form action="eingabe.php" method="post">
        <p>Kategorien:</p>
        <?php

        while ($row_kat = mysqli_fetch_assoc($result_kat)) {
            echo "<input type='checkbox' name='kat[]' value='" . $row_kat["IDKategorie"] . "'>";
            echo "<label>" . $row_kat['Bezeichnung'] . "</label><br>";
        }

        echo "<p>Allergene:</p>";
        while ($row_all = mysqli_fetch_assoc($result_all)) {
            echo "<input type='checkbox' name='all[]' value='" . $row_all["IDAllergen"] . "'>";
            echo "<label>" . $row_all['Kurzzeichen'] . ". " . $row_all['Beschreibung'] . "</label><br>";
        }

        echo "<p>Anzahl:</p>";
        echo "<input type='number' name='anz'>";

        echo "<p>Einheit:</p>";
        echo "<select name='einheit'>";
        echo "<option value='NULL'>None</option>";

        while ($row_ein = mysqli_fetch_assoc($result_einheit)) {
            echo "<option value='" . $row_ein["IDEinheit"] . "'>" . $row_ein['Einheit'] . "</option>";
        }
        ?>

        <option value=""></option>
        </select>

        <p>Produktbezeichnung:</p>
        <input type="text" name="pb">

        <p>Zusatztext:</p>
        <input type="text" name="z">

        <p>Anmerkungen:</p>
        <textarea name="am" cols="30" rows="3"></textarea>

        <p>Preis:</p>
        <input type="number" name="preis" step="0.1"><br><br>

        <input type="submit" name="speichern" value="speichern">
    </form>
</body>
<?php
if (isset($_POST["speichern"])) {

    // $anzahl = empty($_POST['anz']) ? 'NULL' : mysqli_real_escape_string($conn, $_POST['anz']);
    $anzahl = "'" . $_POST['anz'] . "'";
    $einheit = mysqli_real_escape_string($conn, $_POST['einheit']);
    $produkt = "'" . mysqli_real_escape_string($conn, $_POST['pb']) . "'";
    $zusatztext = empty($_POST['z']) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $_POST['z']) . "'";
    $anmerkungen = empty($_POST['am']) ? 'NULL' : "'" . mysqli_real_escape_string($conn, $_POST['am']) . "'";
    $preis = empty($_POST['preis']) ? 'NULL' : mysqli_real_escape_string($conn, $_POST['am']);

    $sql_insert = "INSERT INTO tbl_produkte(Anzahl, FIDEinheit, Produkt, Zusatztext, Anmerkungen, Preis)
    VALUES($anzahl, $einheit, $produkt, $zusatztext, $anmerkungen, $preis)";

    if ($conn->query($sql_insert) === true) {
        $last_id = $conn->insert_id;


        if (isset($_POST['kat'])) {
            foreach ($_POST['kat'] as $kategorie) {
                $sql_insert_Product_kategorie = "INSERT INTO tbl_produkte_kategorien(FIDProdukt, FIDKategorie)
            VALUES ($last_id, $kategorie)";

                if ($conn->query($sql_insert_Product_kategorie) !== True) {
                    echo "Error: " . $sql_insert_Product_kategorie . "<br>" . $conn->error;
                }
            }
        }

        if (isset($_POST['all'])) {
            foreach ($_POST['all'] as $allergene) {
                $sql_insert_Product_allergene = "INSERT INTO tbl_produkt_allergene(FIDProdukt, FIDAllergen)
            VALUES ($last_id, $allergene)";
            }

            if ($conn->query($sql_insert_Product_allergene) !== True) {
                echo "Error: " . $sql_insert_Product_allergene . "<br>" . $conn->error;
            }
        }
        echo "New record created successfully. Last inserted ID is: " . $last_id;
    } else {
        echo "Error: " . $sql_insert . "<br>" . $conn->error;
    }
}
?>

</html>