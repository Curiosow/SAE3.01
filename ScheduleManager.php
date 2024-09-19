<?php

function getDay($date, $day, $semestre, $groupe, $sousgroupe) {
    $newDate = clone $date;
    $newDate->setDate($newDate->format('Y'), $newDate->format('m'), $day);

    $preparedStatement = "SELECT * FROM schedule WHERE DATE(horaire) = $1 AND semestre = $2 AND (nomgroupe = 'TD". $groupe . "' OR nomgroupe = 'TP" . $groupe . $sousgroupe . "' OR nomgroupe = 'CM') ORDER BY version DESC";
    $connexion = pg_connect("host=iutinfo-sgbd.uphf.fr port=5432 dbname=edt user=iutinfo315 password=MDPToSAE22!");
    if(!$connexion) {
        die('La communcation à la base de données a echouée : ' . pg_last_error());
    }

    $result = pg_query_params($connexion, $preparedStatement, array($newDate->format('Y-m-d'), $semestre));

    $version = -1;
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
    }
}

class Course {

    public $code;
    public $typeseance;
    public $typeformation;
    public $collegue;
    public $nomgroupe;
    public $semestre;
    public $noseance;
    public $horaire;
    public $duration;
    public $salle;

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getTypeseance()
    {
        return $this->typeseance;
    }

    /**
     * @param mixed $typeseance
     */
    public function setTypeseance($typeseance)
    {
        $this->typeseance = $typeseance;
    }

    /**
     * @return mixed
     */
    public function getTypeformation()
    {
        return $this->typeformation;
    }

    /**
     * @param mixed $typeformation
     */
    public function setTypeformation($typeformation)
    {
        $this->typeformation = $typeformation;
    }

    /**
     * @return mixed
     */
    public function getCollegue()
    {
        return $this->collegue;
    }

    /**
     * @param mixed $collegue
     */
    public function setCollegue($collegue)
    {
        $this->collegue = $collegue;
    }

    /**
     * @return mixed
     */
    public function getNomgroupe()
    {
        return $this->nomgroupe;
    }

    /**
     * @param mixed $nomgroupe
     */
    public function setNomgroupe($nomgroupe)
    {
        $this->nomgroupe = $nomgroupe;
    }

    /**
     * @return mixed
     */
    public function getSemestre()
    {
        return $this->semestre;
    }

    /**
     * @param mixed $semestre
     */
    public function setSemestre($semestre)
    {
        $this->semestre = $semestre;
    }

    /**
     * @return mixed
     */
    public function getNoseance()
    {
        return $this->noseance;
    }

    /**
     * @param mixed $noseance
     */
    public function setNoseance($noseance)
    {
        $this->noseance = $noseance;
    }

    /**
     * @return mixed
     */
    public function getHoraire()
    {
        return $this->horaire;
    }

    /**
     * @param mixed $horaire
     */
    public function setHoraire($horaire)
    {
        $this->horaire = $horaire;
    }

    /**
     * @return mixed
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    /**
     * @return mixed
     */
    public function getSalle()
    {
        return $this->salle;
    }

    /**
     * @param mixed $salle
     */
    public function setSalle($salle)
    {
        $this->salle = $salle;
    }
}

?>