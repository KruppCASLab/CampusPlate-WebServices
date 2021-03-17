<?php

class FoodStop extends Base {
    public $foodStopId, $name, $description, $streetAddress, $lat, $lng, $hexColor, $foodStopNumber;

    public function __construct($sourceObject) {
        parent::__construct($sourceObject);
    }
}