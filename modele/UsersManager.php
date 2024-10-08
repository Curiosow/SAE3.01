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

function registerGroupe($mail, $promotion, $formation, $groupe, $sousgroupe) {
    $preparedStatement = "UPDATE public.users SET promotion  = $1, formation  = $2::typeformation, groupe = $3::parcours, sousgroupe = $4 WHERE mail = $5;";
    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    pg_query_params($connexion, $preparedStatement, array($promotion, $formation, $groupe, $sousgroupe, $mail));
}

function getAccountFromMail($mail) {
    $preparedStatement = "SELECT id, mail, password, role, promotion, formation, groupe, sousgroupe FROM users WHERE mail = $1";
    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($mail));
    return $result;
}