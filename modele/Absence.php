<?php

class Absence
{

    public $id;
    public $start;
    public $end;
    public $reason;
    public $collegue;

    /**
     * @param $id
     * @param $start
     * @param $end
     * @param $reason
     * @param $collegue
     */
    public function __construct($id, $start, $end, $reason, $collegue)
    {
        $this->id = $id;
        $this->start = $start;
        $this->end = $end;
        $this->reason = $reason;
        $this->collegue = $collegue;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @param mixed $start
     */
    public function setStart($start): void
    {
        $this->start = $start;
    }

    /**
     * @return mixed
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * @param mixed $end
     */
    public function setEnd($end): void
    {
        $this->end = $end;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @param mixed $reason
     */
    public function setReason($reason): void
    {
        $this->reason = $reason;
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
    public function setCollegue($collegue): void
    {
        $this->collegue = $collegue;
    }



}