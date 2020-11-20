<?php
class FoodStop extends Base {
  public $foodStopId, $name, $description, $lat, $lng;

  public function __construct($sourceObject) {
    parent::__construct($sourceObject);
  }
}