<?php

class lesson
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
    public $enseignementShortName;
    public $enseignementLongName;
    public $collegueFullName;
    public $discipline;
    public $version;
    public $exam;

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

    /**
     * @return mixed
     */
    public function getEnseignementShortName()
    {
        return $this->enseignementShortName;
    }

    /**
     * @param mixed $enseignementShortName
     */
    public function setEnseignementShortName($enseignementShortName)
    {
        $this->enseignementShortName = $enseignementShortName;
    }

    /**
     * @return mixed
     */
    public function getEnseignementLongName()
    {
        return $this->enseignementLongName;
    }

    /**
     * @param mixed $enseignementLongName
     */
    public function setEnseignementLongName($enseignementLongName)
    {
        $this->enseignementLongName = $enseignementLongName;
    }

    /**
     * @return mixed
     */
    public function getCollegueFullName()
    {
        return $this->collegueFullName;
    }

    /**
     * @param mixed $collegueFullName
     */
    public function setCollegueFullName($collegueFullName)
    {
        $this->collegueFullName = $collegueFullName;
    }

    /**
     * @return mixed
     */
    public function getDiscipline()
    {
        return $this->discipline;
    }

    /**
     * @param mixed $discipline
     */
    public function setDiscipline($discipline): void
    {
        $this->discipline = $discipline;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version): void
    {
        $this->version = $version;
    }

    /**
     * @return mixed
     */
    public function getExam()
    {
        return $this->exam;
    }

    /**
     * @param mixed $exam
     */
    public function setExam($exam): void
    {
        $this->exam = $exam;
    }

    public function __toString()
    {
        return 'code=' . $this->code . "\ttype=" . $this->typeseance . "\tformation=" . $this->typeformation .
            "\tcollegue=" . $this->collegue . "\tgroupe=" . $this->nomgroupe . "\tsemestre=" . $this->semestre .
            "\thoraire=" . $this->horaire . "\tduration=" . $this->duration .
            "\tsalle=" . $this->salle . "\tenseignement=" . $this->enseignementShortName . "\tdiscipline=" . $this->discipline . "\texam=" . $this->exam;
    }

}
