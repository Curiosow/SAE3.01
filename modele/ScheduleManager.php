<?php
include "Lesson.php";
include "Database.php";

function getDay($date, $day, $semestre, $groupe, $sousgroupe, $formation)
{
    $newDate = clone $date;
    $newDate->setDate($newDate->format('Y'), $newDate->format('m'), $day);

    $preparedStatement = "SELECT * FROM schedule WHERE DATE(horaire) = $1 AND semestre = $2 AND (typeformation = $3 OR typeformation = 'MUT') AND (nomgroupe = $4 OR nomgroupe = $5 OR nomgroupe = 'CM') ORDER BY version DESC";
    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($newDate->format('Y-m-d'), $semestre, $formation, 'TD' . $groupe, 'TP' . $groupe . $sousgroupe));

    $version = -1;
    $courses = [];

    while ($row = pg_fetch_assoc($result)) {
        if($version == -1) {
            $v = $row['version'];
            $version = intval($v);
        } else if($version != intval($row['version']))
            continue;

        $course = new Lesson();
        $course->setCode($row['code']);
        $course->setTypeseance($row['typeseance']);
        $course->setTypeformation($row['typeformation']);
        $course->setCollegue($row['collegue']);
        $course->setNomgroupe($row['nomgroupe']);
        $course->setSemestre($row['semestre']);
        $course->setNoseance($row['noseance']);
        $course->setHoraire($row['horaire']);
        $course->setDuration($row['duration']);
        $course->setSalle(getSalle($course->getTypeformation(), $course->getCode(), $course->getTypeseance(), $course->getSemestre(), $course->getNomgroupe(), $course->getCollegue(), $course->getNoseance()));
        $courses[] = $course;
    }

    return $courses;
}

function getSemestre($promotion, $date)
{
    if ($date instanceof DateTime)
        $date = $date->format('Y-m-d');
    $month = date('n', strtotime($date));

    $s1 = array(1, 2, 9, 10, 11, 12);
    switch ($promotion) {
        case '1':
            return (in_array($month, $s1)) ? '1' : '2';
        case '2':
            return (in_array($month, $s1)) ? '3' : '4';
        case '3':
            return (in_array($month, $s1)) ? '5' : '6';
        default:
            return null;
    }
}

function getVersion()
{
    $query = "SELECT MAX(version) AS version FROM schedule";
    $connexion = Database::getInstance()->getConnection();

    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query($connexion, $query);
    if (!$result) {
        die('La requête a échouée : ' . pg_last_error());
    }

    return pg_fetch_assoc($result)['version'];
}

function getSalle($formation, $code, $seance, $semestre, $groupe, $collegue, $noseance)
{
    $query = "SELECT salle, version FROM schedulesalle WHERE typeformation = $1 AND code = $2 AND typeseance = $3 AND semestre = $4 AND nomgroupe = $5 AND collegue = $6 AND noseance = $7 ORDER BY version DESC";
    $connexion = Database::getInstance()->getConnection();

    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $query, array($formation, $code, $seance, $semestre, $groupe, $collegue, $noseance));
    if (!$result) {
        die('La requête a échouée : ' . pg_last_error());
    }

    if(!isset(pg_fetch_assoc($result)['salle']))
        return null;

    return pg_fetch_assoc($result)['salle'];
}

/*
 * /Users/curiosow/Documents/Cours/BUT2/SAE-EMPLOIDUTEMPS/SAE3.01/modele/ScheduleManager.php
: Trying to access array offset on false in

 *
 */