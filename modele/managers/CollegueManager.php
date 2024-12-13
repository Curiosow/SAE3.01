<?php

function getCollegueId($mail) {
    if(!isset($mail)) {
        return '';
    }

    $preparedStatement = "SELECT id FROM collegue WHERE mail=$1";

    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($mail));

    $row = pg_fetch_assoc($result);
    return $row['id'];
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