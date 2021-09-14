<?php

class FoodStop extends Base {
    public $foodStopId, $name, $description, $streetAddress, $lat, $lng, $hexColor, $foodStopNumber;

    public function __construct($sourceObject = null) {
        parent::__construct($sourceObject);
    }
}