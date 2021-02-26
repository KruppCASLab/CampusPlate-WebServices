<?php
class Reservation extends Base {
  public $reservationId, $userId, $listingId, $quantity, $status, $code, $timeCreated, $timeExpired;

  static $RESERVATION_PLACED = 0;
  static $RESERVATION_FULFILLED = 1;

  static $RESERVATION_RETURN_SUCCESS = 0;
  static $RESERVATION_RETURN_QUANTITY_NOT_AVAILABLE = 1;
  static $RESERVATION_LISTING_NOT_AVAILABLE = 2;

  public function __construct($sourceObject) {
    parent::__construct($sourceObject);
  }
}