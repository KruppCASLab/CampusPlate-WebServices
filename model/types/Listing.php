<?php

require_once(__DIR__ . "/Base.php");

class Listing extends Base {

  public $listingId, $userId, $title, $locationDescription, $lat, $lng, $creationTime, $quantity, $image;

  public function __construct($sourceObject) {
    parent::__construct($sourceObject);
  }

}