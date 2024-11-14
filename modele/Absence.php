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

}