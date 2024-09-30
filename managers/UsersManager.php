<?php
include "Database.php";

function hasAccount($mail) {
    $preparedStatement = "SELECT count(*) FROM users WHERE mail = $1";
    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($mail));

    return pg_fetch_assoc($result)['count'] == 1;
}

function registerUser($mail, $password) {
    $preparedStatement = "INSERT INTO users (mail, password) VALUES ($1, $2)";
    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    pg_query_params($connexion, $preparedStatement, array($mail, $password));
}

function getAccountFromMail($mail) {
    $preparedStatement = "SELECT id, mail, password, role FROM users WHERE mail = $1";
    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($mail));
    return $result;
}