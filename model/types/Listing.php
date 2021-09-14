<?php

require_once(__DIR__ . "/Base.php");

class Listing extends Base {

    public $listingId, $foodStopId, $userId, $title, $description, $creationTime, $expirationTime, $quantity, $weightOunces, $image, $quantityRemaining;

    public function __construct($sourceObject = null) {
        parent::__construct($sourceObject);
    }

}