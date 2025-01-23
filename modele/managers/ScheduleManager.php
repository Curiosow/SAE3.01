<?php
include "../modele/Lesson.php";
//include "../modele/Database.php";
include_once "EnseignementManager.php";
include_once "CollegueManager.php";

function getDay($date, $day, $semestre, $groupe, $sousgroupe, $formation, $teacherEdt = false)
{

    $newDate = clone $date;
    $newDate->setDate($newDate->format('Y'), $newDate->format('m'), $day);

    /*$preparedStatement = "SELECT * FROM schedule
         WHERE DATE(horaire) = $1 AND semestre = $2 AND (typeformation = $3 OR typeformation = 'MUT') AND (nomgroupe = $4 OR nomgroupe = $5 OR nomgroupe = 'CM') 
         ORDER BY version DESC";*/

    if(!$teacherEdt) {
        $preparedStatement = "
            SELECT s.*, e.long AS enseignement_longname, e.court AS enseignement_shortname, c.prenom, c.nom, e.discipline AS discipline, ss.salle
            FROM schedule s
            LEFT JOIN enseignement e ON s.code = e.code
            LEFT JOIN collegue c ON s.collegue = c.id
            LEFT JOIN schedulesalle ss ON s.typeformation = ss.typeformation AND s.code = ss.code AND s.typeseance = ss.typeseance AND s.semestre = ss.semestre AND s.nomgroupe = ss.nomgroupe AND s.collegue = ss.collegue AND s.noseance = ss.noseance
            WHERE DATE(s.horaire) = $1 AND s.semestre = $2 AND (s.typeformation = $3 OR s.typeformation = 'MUT') AND (s.nomgroupe = $4 OR s.nomgroupe = $5 OR s.nomgroupe = 'CM')
            ORDER BY s.version DESC";
    } else {
        $preparedStatement = "
            SELECT s.*, e.long AS enseignement_longname, e.court AS enseignement_shortname, c.prenom, c.nom, e.discipline AS discipline, ss.salle
            FROM schedule s
            LEFT JOIN enseignement e ON s.code = e.code
            LEFT JOIN collegue c ON s.collegue = c.id
            LEFT JOIN schedulesalle ss ON s.typeformation = ss.typeformation AND s.code = ss.code AND s.typeseance = ss.typeseance AND s.semestre = ss.semestre AND s.nomgroupe = ss.nomgroupe AND s.collegue = ss.collegue AND s.noseance = ss.noseance
            WHERE DATE(s.horaire) = $1 AND s.semestre = $2 AND (s.typeformation = $3 OR s.typeformation = 'MUT') AND (s.nomgroupe = $4 OR s.nomgroupe = $5 OR s.nomgroupe = 'CM') AND s.collegue = $6
            ORDER BY s.version DESC";
    }


    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    if(!$teacherEdt)
        $result = pg_query_params($connexion, $preparedStatement, array($newDate->format('Y-m-d'), $semestre, $formation, 'TD' . $groupe, 'TP' . $groupe . $sousgroupe));
    else
        $result = pg_query_params($connexion, $preparedStatement, array($newDate->format('Y-m-d'), $semestre, $formation, 'TD' . $groupe, 'TP' . $groupe . $sousgroupe, $_COOKIE['collegue']));

    $version = -1;
    $courses = [];

    while ($row = pg_fetch_assoc($result)) {
        if($version == -1) {
            $v = $row['version'];
            $version = intval($v);
            //$version = intval(returnVersion());
        } else if($version != intval($row['version']))
            continue;

        /*$course = new Lesson();
        $course->setCode($row['code']);
        $course->setTypeseance($row['typeseance']);
        $course->setTypeformation($row['typeformation']);
        $course->setCollegue($row['collegue']);
        $course->setNomgroupe($row['nomgroupe']);
        $course->setSemestre($row['semestre']);
        $course->setNoseance($row['noseance']);
        $course->setHoraire($row['horaire']);
        $course->setDuration($row['duration']);
        $course->setEnseignementLongName(getEnseignementFullName($row['code']));
        $course->setEnseignementShortName(getEnseignementShortName($row['code']));
        $course->setCollegueFullName(getCollegueFullName($row['collegue']));
        $course->setSalle(getSalle($course->getTypeformation(), $course->getCode(), $course->getTypeseance(), $course->getSemestre(), $course->getNomgroupe(), $course->getCollegue(), $course->getNoseance()));
        $course->setDiscipline(getDiscipline($row['code']));
        $courses[] = $course;*/
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
        //$course->setVersion($version);
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
// Récupère la version actuelle
function getCurrentVersion()
{
    $query = "SELECT MAX(version) AS version FROM schedule";
    $connexion = Database::getInstance()->getConnection();

    if(!$connexion) {
        die('La communication à la base de données a échouée : ' . pg_last_error());
    }

    $result = pg_query($connexion, $query);
    if (!$result) {
        die('La requête a échouée : ' . pg_last_error());
    }

    return pg_fetch_assoc($result)['version'];
}

// Récupère la version précédente
function getPreviousVersion($currentVersion)
{
    $query = "SELECT MAX(version) AS version FROM schedule WHERE version < $1";
    $connexion = Database::getInstance()->getConnection();

    if(!$connexion) {
        die('La communication à la base de données a échouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $query, array($currentVersion));
    if (!$result) {
        die('La requête a échouée : ' . pg_last_error());
    }

    $previousVersion = pg_fetch_assoc($result)['version'];
    return intval($previousVersion);
}


function getDayPreviousVersion($date, $day, $semestre, $groupe, $sousgroupe, $formation)
{
    $newDate = clone $date;
    $newDate->setDate($newDate->format('Y'), $newDate->format('m'), $day);

    $preparedStatement = "
        SELECT s.*, e.long AS enseignement_longname, e.court AS enseignement_shortname, c.prenom, c.nom, e.discipline AS discipline, ss.salle
        FROM schedule s
        LEFT JOIN enseignement e ON s.code = e.code
        LEFT JOIN collegue c ON s.collegue = c.id
        LEFT JOIN schedulesalle ss ON s.typeformation = ss.typeformation AND s.code = ss.code AND s.typeseance = ss.typeseance AND s.semestre = ss.semestre AND s.nomgroupe = ss.nomgroupe AND s.collegue = ss.collegue AND s.noseance = ss.noseance
        WHERE DATE(s.horaire) = $1 AND s.semestre = $2 AND (s.typeformation = $3 OR s.typeformation = 'MUT') AND (s.nomgroupe = $4 OR s.nomgroupe = $5 OR s.nomgroupe = 'CM') AND s.version  = $6";

    $connexion = Database::getInstance()->getConnection();
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($newDate->format('Y-m-d'), $semestre, $formation, 'TD' . $groupe, 'TP' . $groupe . $sousgroupe, getPreviousVersion(getCurrentVersion())));

    $courses = [];

    while ($row = pg_fetch_assoc($result)) {
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