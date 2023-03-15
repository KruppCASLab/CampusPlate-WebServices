<?php

require_once(__DIR__ . "/Base.php");
class Hours extends Base
{
    public $hoursId, $foodStopId, $dayOfWeek, $timeOpen, $timeClose;

    public function __construct($sourceObject = null) {
        parent::__construct ($sourceObject);
    }
}