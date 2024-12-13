<?php
include ("../modele/Lesson.php");
include ("../modele/Database.php");
include_once "EnseignementManager.php";
include_once "CollegueManager.php";

function getDay($date, $day, $semestre, $groupe, $sousgroupe, $formation)
{
    $newDate = clone $date;
    $newDate->setDate($newDate->format('Y'), $newDate->format('m'), $day);

    $preparedStatement = "
        SELECT s.*, e.long AS enseignement_longname, e.court AS enseignement_shortname, c.prenom, c.nom, e.discipline AS discipline, ss.salle
        FROM schedule s
        LEFT JOIN enseignement e ON s.code = e.code
        LEFT JOIN collegue c ON s.collegue = c.id
        LEFT JOIN schedulesalle ss ON s.typeformation = ss.typeformation AND s.code = ss.code AND s.typeseance = ss.typeseance AND s.semestre = ss.semestre AND s.nomgroupe = ss.nomgroupe AND s.collegue = ss.collegue AND s.noseance = ss.noseance
        WHERE DATE(s.horaire) = $1 AND s.semestre = $2 AND (s.typeformation = $3 OR s.typeformation = 'MUT') AND (s.nomgroupe = $4 OR s.nomgroupe = $5 OR s.nomgroupe = 'CM')
        ORDER BY s.version DESC";

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
        $course->setEnseignementLongName($row['enseignement_longname']);
        $course->setEnseignementShortName($row['enseignement_shortname']);
        $course->setCollegueFullName($row['prenom'] . ' ' . $row['nom']);
        $course->setSalle($row['salle']);
        $course->setDiscipline($row['discipline']);
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
