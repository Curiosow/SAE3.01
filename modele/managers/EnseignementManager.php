<?php
require_once __DIR__ . "/../Database.php";

function getDisciplineColors() {
    $preparedStatement = "SELECT * FROM disciplinecouleur";

    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query($connexion, $preparedStatement);

    $disciplineColors = [];
    while ($row = pg_fetch_assoc($result)) {
        $disciplineColors[$row['discipline']] = $row['couleur'];
    }

    return $disciplineColors;
}