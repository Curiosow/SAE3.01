<?php

function getCollegueFullName($id) {
    if(!isset($id)) {
        return '';
    }

    $preparedStatement = "SELECT * FROM collegue WHERE id=$1";

    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($id));

    $row = pg_fetch_assoc($result);
    return $row['prenom'] . ' ' . $row['nom'];
}

function isACollegue($mail) {
    if(!isset($mail)) {
        return '';
    }

    $preparedStatement = "SELECT * FROM collegue WHERE mail=$1";


    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($mail));
    $row = pg_fetch_assoc($result);
    return !empty($row);
}