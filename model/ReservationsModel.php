<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Reservation.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/ListingsModel.php");


class ReservationsModel {
    static public function createReservation(Reservation $reservation) {
        $sql = "INSERT INTO tblReservations(userId, listingId, quantity, status, code, timeCreated, timeExpired) VALUES (?,?,?,?,?,?,?)";
        Database::executeSql($sql, "iiiiiii", array($reservation->userId, $reservation->listingId, $reservation->quantity, $reservation->status, $reservation->code, $reservation->timeCreated, $reservation->timeExpired));

        return !isset(Database::$lastError);
    }

    static public function fulfillReservation(Reservation $reservation) {
        $sql = "UPDATE tblReservations SET quantity = ?, status = ? WHERE reservationId = ?";
        Database::executeSql($sql, "iii", array($reservation->quantity, Reservation::$RESERVATION_STATUS_FULFILLED, $reservation->reservationId));

        return !isset(Database::$lastError);
    }

    static public function getReservationQuantity(int $listingId) {
        // Get the total number of items that have been fulfilled through reservation or on demand OR or placed but have not expired
        $sql = "SELECT SUM(quantity) from tblReservations where listingId = ? AND (status = ? OR status = ? OR (status = ? AND ? < timeExpired) )";
        $results = Database::executeSql($sql, "iiiii", array($listingId, Reservation::$RESERVATION_STATUS_FULFILLED, Reservation::$RESERVATION_STATUS_ON_DEMAND, Reservation::$RESERVATION_STATUS_PLACED, time()));
        return $results[0]["SUM(quantity)"];
    }

    static public function getUserReservations(int $userId) {
        // Only return reservations that were placed and not expired
        $sql = "SELECT * from tblReservations where userId = ? AND ? < timeExpired AND status = ? ";
        $results = Database::executeSql($sql, "iii", array($userId, time(), Reservation::$RESERVATION_STATUS_PLACED));

        $updatedResults = array();
        foreach($results as $result) {
            $result["listing"] = ListingsModel::getListing($result["listingId"]);
            array_push($updatedResults, $result);
        }
        return $updatedResults;
    }

    static public function getFoodStopReservations(int $foodStopId) {
        // Only return reservations that have not expired AND have not been fulfilled
        $sql = "SELECT r.reservationId, r.listingId, r.quantity, r.status, r.code, r.timeCreated, r.timeExpired FROM tblReservations r JOIN tblListings l ON r.listingId = l.listingId WHERE l.foodStopId = ? AND ? < timeExpired AND status = ?";
        $results = Database::executeSql($sql, "iii", array($foodStopId, time(), Reservation::$RESERVATION_STATUS_PLACED));
        return $results;
    }

    static public function getFulfilledReservations(int $listingId): array {
        $sql = "SELECT * from tblReservations where listingId = ? AND (status = ? OR status = ?)";
        return Database::executeSql($sql, "iii", array($listingId, Reservation::$RESERVATION_STATUS_FULFILLED, Reservation::$RESERVATION_STATUS_ON_DEMAND));
    }

    static public function deleteReservationsFromListing(int $listingId): bool {
        $sql = "DELETE from tblReservations where listingId = ?";
        Database::executeSql($sql, "i", array($listingId));

        return !isset(Database::$lastError);
    }

    static public function deleteReservationForUser(int $reservationId, int $userId): bool {
        $sql = "DELETE from tblReservations where reservationId = ? AND userId = ?";
        Database::executeSql($sql, "ii", array($reservationId, $userId));

        return !isset(Database::$lastError);
    }


}