<?php
include "../modele/Database.php";

class UserControleur
{
    public function __construct() {

    }
    public function hasAccount($mail) {
        $preparedStatement = "SELECT count(*) FROM users WHERE mail = $1";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        $result = pg_query_params($connexion, $preparedStatement, array($mail));

        return pg_fetch_assoc($result)['count'] == 1;
    }

    function registerUser($mail, $password, $token) {
        $preparedStatement = "INSERT INTO users (mail, password, token) VALUES ($1, $2, $3)";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($mail, $password, $token));
    }

    public function getAccountFromMail($mail) {
        $preparedStatement = "SELECT id, mail, password, role, verified FROM users WHERE mail = $1";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        $result = pg_query_params($connexion, $preparedStatement, array($mail));
        return $result;
    }

    function updateAccountVerification($token) {
        $preparedStatement = "UPDATE users SET verified = true, token = NULL WHERE token = $1";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($token));
    }

    function updateAccountPassword($token, $password) {
        $preparedStatement = "UPDATE users SET password = $1, forgotoken = NULL WHERE forgotoken = $2";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($password, $token));
    }

    function setAccountForgotToken($mail, $token) {
        $preparedStatement = "UPDATE users SET forgotoken = $1 WHERE mail = $2";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communcation à la base de données a echouée : ' . pg_last_error());
        }

        pg_query_params($connexion, $preparedStatement, array($token, $mail));
    }
}