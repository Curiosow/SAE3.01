<?php
include_once "../modele/Database.php";
include "../modele/Absence.php";

class AbsenceControleur
{
    public function __construct()
    {
    }

    public function createAbsence($start, $end, $reason)
    {
        $preparedStatement = "INSERT INTO absences (start, end, reason, collegue) VALUES ($1, $2, $3, $4)";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communication à la base de données a échouée : ' . pg_last_error());
        }

        $collegue =
        $result = pg_query_params($connexion, $preparedStatement, array($start, $end, $reason));
    }

}