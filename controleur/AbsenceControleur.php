<?php
include_once "../modele/Database.php";
include "../modele/Absence.php";

class AbsenceControleur
{
    public function __construct()
    {
    }

    public function addAbsence($start, $end, $reason)
    {
        $preparedStatement = "INSERT INTO absences (start, \"end\", reason, collegue) VALUES ($1, $2, $3, $4)";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communication à la base de données a échouée : ' . pg_last_error());
        }

        $collegue = 'Anonyme';
        if(isset($_COOKIE['collegue']) && $_COOKIE['collegue'] != "NONE")
            $collegue = $_COOKIE['collegue'];
        else if (isset($_COOKIE['mail']) && $_COOKIE['mail'] != "NONE")
            $collegue = $_COOKIE['mail'];

        pg_query_params($connexion, $preparedStatement, array($start, $end, $reason, $collegue));
    }

    public function getAllAbsences() {
        $preparedStatement = "SELECT * FROM absences";
        $connexion = Database::getInstance()->getConnection();
        if(!$connexion) {
            die('La communication à la base de données a échouée : ' . pg_last_error());
        }
        $result = pg_query($connexion, $preparedStatement);
        $absences = array();
        while ($row = pg_fetch_assoc($result)) {
            $absences[] = new Absence($row['id'], $row['start'], $row['end'], $row['reason'], $row['collegue']);
        }
        return $absences;
    }

}