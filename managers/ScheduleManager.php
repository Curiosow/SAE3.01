<?php
include "Course.php";

function getDay($date, $day, $semestre, $groupe, $sousgroupe, $formation) {
    $newDate = clone $date;
    $newDate->setDate($newDate->format('Y'), $newDate->format('m'), $day);

    $preparedStatement = "SELECT * FROM schedule WHERE DATE(horaire) = $1 AND semestre = $2 AND (typeformation = $3 OR typeformation = 'MUT') AND (nomgroupe = 'TD" . $groupe . "' OR nomgroupe = 'TP" . $groupe . $sousgroupe . "' OR nomgroupe = 'CM') ORDER BY version DESC";
    $connexion = pg_connect("host=iutinfo-sgbd.uphf.fr port=5432 dbname=edt user=iutinfo315 password=MDPToSAE22!");
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($newDate->format('Y-m-d'), $semestre, $formation));

    $version = -1;
    $courses = [];

    while ($row = pg_fetch_assoc($result)) {
        if($version == -1) {
            $v = $row['version'];
            $version = intval($v);
        } else if($version != intval($row['version']))
            continue;

        $course = new Course();
        $course->setCode($row['code']);
        $course->setTypeseance($row['typeseance']);
        $course->setTypeformation($row['typeformation']);
        $course->setCollegue($row['collegue']);
        $course->setNomgroupe($row['nomgroupe']);
        $course->setSemestre($row['semestre']);
        $course->setNoseance($row['noseance']);
        $course->setHoraire($row['horaire']);
        $course->setDuration($row['duration']);
        $course->setSalle($row['salle']);
        $courses[] = $course;
    }

    return $courses;
}

function getSemestre($promotion, $date) {
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