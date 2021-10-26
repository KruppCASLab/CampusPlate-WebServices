<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Reservation.php");
require_once(__DIR__ . "/types/FoodStop.php");

class ReportingModel {
    static private function getLastWeekTimestamp() : int {
        return time() - (7 * 24 * 60 * 60);
    }

    static public function getTotalItemsRecovered() : int {
        $sql = "SELECT count(*) as total FROM tblReservations WHERE status = ? OR status = ? ";
        $results = Database::executeSql($sql, "ii", array(Reservation::$RESERVATION_STATUS_FULFILLED, Reservation::$RESERVATION_STATUS_ON_DEMAND));
        return $results[0]["total"];
    }

    static public function getItemsRecoveredLastWeek() : int {
        $sql = "SELECT count(*) as total FROM tblReservations WHERE (status = ? OR status = ?) AND timeCreated > ? ";
        $lastWeekTimestamp = self::getLastWeekTimestamp();
        $results = Database::executeSql($sql, "iii", array(Reservation::$RESERVATION_STATUS_FULFILLED, Reservation::$RESERVATION_STATUS_ON_DEMAND, $lastWeekTimestamp));
        return $results[0]["total"];
    }

    static public function getItemsPerDay() {
        $sql = "SELECT DATE(FROM_UNIXTIME(timeCreated)) as createdDate,SUM(tblReservations.quantity) as numPerDate FROM tblReservations WHERE status = ? or status = ? GROUP BY DATE(FROM_UNIXTIME(timeCreated)) ORDER BY createdDate";
        $results = Database::executeSql($sql, "ii", array(Reservation::$RESERVATION_STATUS_FULFILLED, Reservation::$RESERVATION_STATUS_ON_DEMAND));
        return $results;
    }

}