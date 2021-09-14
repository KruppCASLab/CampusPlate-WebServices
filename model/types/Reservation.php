<?php

require_once(__DIR__ . "/Base.php");

class Reservation extends Base {
    public $reservationId, $userId, $listingId, $quantity, $status, $code, $timeCreated, $timeExpired;

    static $RESERVATION_STATUS_PLACED = 0;
    static $RESERVATION_STATUS_FULFILLED = 1;
    // Used if a person picks up food at that particular location
    static $RESERVATION_STATUS_ON_DEMAND = 2;

    static $RESERVATION_RETURN_CODE_SUCCESS = 0;
    static $RESERVATION_RETURN_CODE_QUANTITY_NOT_AVAILABLE = 1;
    static $RESERVATION_RETURN_CODE_LISTING_NOT_AVAILABLE = 2;

    public function __construct($sourceObject = null) {
        parent::__construct($sourceObject);
    }
}