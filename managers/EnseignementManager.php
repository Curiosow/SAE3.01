<?php

function getEnseignementFullName($id) {
    if(!isset($id)) {
        return '';
    }

    $preparedStatement = "SELECT long FROM enseignement WHERE code=$1";
    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($id));

    return pg_fetch_assoc($result)['long'];
}

function getEnseignementShortName($id) {
    if(!isset($id)) {
        return '';
    }

    $preparedStatement = "SELECT court FROM enseignement WHERE code=$1";
    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($id));

    return pg_fetch_assoc($result)['court'];
}