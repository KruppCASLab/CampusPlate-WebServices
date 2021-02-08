<?php
class FoodStop extends Base {
  public $foodStopId, $name, $description, $lat, $lng, $hexColor, $foodStopNumber;

  public function __construct($sourceObject) {
    parent::__construct($sourceObject);
  }
}