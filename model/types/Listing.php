<?php

require_once(__DIR__ . "/Base.php");

class Listing extends Base {

  public $listingId, $userId, $title, $lat, $lng, $creationTime, $quantity;

  public function __construct($sourceObject) {
    parent::__construct($sourceObject);
  }

}