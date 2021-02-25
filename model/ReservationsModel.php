<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Reservation.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");


class ReservationsModel {
  static public function createReservation(Reservation $reservation) {
    $sql = "INSERT INTO tblReservations(userId, listingId, quantity, status, code, timeCreated, timeExpired) VALUES (?,?,?,?,?,?,?)";
    Database::executeSql($sql, "iiiiiii", array($reservation->userId, $reservation->listingId, $reservation->quantity, $reservation->status, $reservation->code, $reservation->timeCreated, $reservation->timeExpired));

    return ! isset(Database::$lastError);
  }

  static public function getReservationQuantity(int $listingId) {
    // Get the total number of items that have been reserved or fulfilled
    $sql = "SELECT SUM(quantity) from tblReservations where listingId = ? AND (status = ? OR status = ?)";
    $results = Database::executeSql($sql, "iii", array($listingId, Reservation::$RESERVATION_FULFILLED, Reservation::$RESERVATION_PLACED));
    return $results[0]["SUM(quantity)"];
  }

  static public function getUserReservations(int $userId) {
    $sql = "SELECT * from tblReservations where userId = ?";
    $results = Database::executeSql($sql, "i", array($userId));
    return $results;
  }

}