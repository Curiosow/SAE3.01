<?php

class Course
{
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
