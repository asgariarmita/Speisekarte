<?php
require("includes/conn.inc.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Speisekarte</title>
</head>

<body>

    <?php

    $excludeAllergens = [];
    if (isset($_POST['allergens'])) {
        $excludeAllergens = $_POST['allergens'];
    }

    // Fetch all allergens for the checkboxes
    $allergenResult = $conn->query("SELECT * FROM tbl_allergene");

    echo '<form method="POST" action="speisekarte.php">';
    while ($allergen = $allergenResult->fetch_assoc()) {
        // Check if current allergen was previously selected
        $checked = in_array($allergen['IDAllergen'], $excludeAllergens) ? 'checked' : '';
        echo '<input type="checkbox" name="allergens[]" value="' . $allergen['IDAllergen'] . '"' . $checked . '>' . $allergen['Kurzzeichen'] . ': ' . $allergen['Beschreibung'] . '<br>';
    }
    echo '<input type="submit" name="submit" value="Filter">';
    echo '</form>';

    // Fetch categories with no subcategories
    $result1 = $conn->query("SELECT * FROM tbl_kategorien WHERE FIDKategorie IS NULL");

    while ($cat = $result1->fetch_assoc()) {
        echo "<h2>{$cat['Bezeichnung']}</h2>";

        // Fetch products directly under this category
        $productQuery = "SELECT * FROM tbl_produkte_kategorien pk 
                     JOIN tbl_produkte p ON pk.FIDProdukt = p.IDProdukt
                     WHERE pk.FIDKategorie = {$cat['IDKategorie']}";

        if (!empty($excludeAllergens)) {
            $allergenString = implode(",", $excludeAllergens);
            $productQuery .= " AND p.IDProdukt NOT IN (
                            SELECT pa.FIDProdukt FROM tbl_produkt_allergene pa 
                            WHERE pa.FIDAllergen IN ({$allergenString})
                          )";
        }

        $result3 = $conn->query($productQuery);
        while ($prod = $result3->fetch_assoc()) {

            // Fetch allergens for each product
            $result4 = $conn->query("SELECT a.Kurzzeichen FROM tbl_produkt_allergene pa 
                                 JOIN tbl_allergene a ON pa.FIDAllergen = a.IDAllergen 
                                 WHERE pa.FIDProdukt = {$prod['IDProdukt']}");

            $allergens = [];
            while ($allergen = $result4->fetch_assoc()) {
                $allergens[] = $allergen['Kurzzeichen'];
            }

            echo "- {$prod['Produkt']} (" . implode(', ', $allergens) . ") {$prod['Preis']}<br>";
        }

        // Fetch subcategories for each category
        $result2 = $conn->query("SELECT * FROM tbl_kategorien WHERE FIDKategorie = {$cat['IDKategorie']}");
        while ($subCat = $result2->fetch_assoc()) {
            echo "<h3>{$subCat['Bezeichnung']}</h3>";

            // Fetch products for each subcategory
            $productSubCatQuery = "SELECT * FROM tbl_produkte_kategorien pk 
                               JOIN tbl_produkte p ON pk.FIDProdukt = p.IDProdukt
                               WHERE pk.FIDKategorie = {$subCat['IDKategorie']}";

            if (!empty($excludeAllergens)) {
                $allergenString = implode(",", $excludeAllergens);
                $productSubCatQuery .= " AND p.IDProdukt NOT IN (
                                     SELECT pa.FIDProdukt FROM tbl_produkt_allergene pa 
                                     WHERE pa.FIDAllergen IN ({$allergenString})
                                   )";
            }

            $result3 = $conn->query($productSubCatQuery);
            while ($prod = $result3->fetch_assoc()) {

                // Fetch allergens for each product
                $result4 = $conn->query("SELECT a.Kurzzeichen FROM tbl_produkt_allergene pa 
                                     JOIN tbl_allergene a ON pa.FIDAllergen = a.IDAllergen 
                                     WHERE pa.FIDProdukt = {$prod['IDProdukt']}");

                $allergens = [];
                while ($allergen = $result4->fetch_assoc()) {
                    $allergens[] = $allergen['Kurzzeichen'];
                }

                echo "- {$prod['Produkt']} (" . implode(', ', $allergens) . ") {$prod['Preis']}<br>";
            }
        }
    }
    ?>

</body>

</html>