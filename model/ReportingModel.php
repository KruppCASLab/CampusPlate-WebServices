<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Reservation.php");
require_once(__DIR__ . "/types/FoodStop.php");

class ReportingModel {
    static private function getLastWeekTimestamp() : int {
        return time() - (7 * 24 * 60 * 60);
    }

    static public function getNumberOfUsersUsingAppInPastWeek(): int {
        $sql = "select COUNT(DISTINCT userName) as total from tblUsers INNER JOIN tblCredentials on tblCredentials.userId = tblUsers.userId where tblCredentials.lastUsed > ?";
        $lastWeekTimestamp = self::getLastWeekTimestamp();
        $results = Database::executeSql($sql, "i", array($lastWeekTimestamp));
        return $results[0]["total"] ?? 0;
    }

    static public function getNumberOfCredentialsCreatedInAppInPastWeek(): int {
        $sql = "select COUNT(DISTINCT userName) as total from tblUsers INNER JOIN tblCredentials on tblCredentials.userId = tblUsers.userId where tblCredentials.created > ?";

        $lastWeekTimestamp = self::getLastWeekTimestamp();
        $results = Database::executeSql($sql, "i", array($lastWeekTimestamp));
        return $results[0]["total"] ?? 0;
    }

    static public function getAdminUsers() : array {
        //TODO: Change 1 to bind to a role number from model
        $sql = "SELECT userName from tblUsers where role = 1";
        return Database::executeSql($sql);
    }

    static public function getFoodStopManagers() : array {
        $sql = "SELECT tblUsers.userName, tblFoodStops.name FROM tblFoodStopManagers JOIN tblUsers on tblFoodStopManagers.userId = tblUsers.userId JOIN tblFoodStops on tblFoodStopManagers.foodStopId = tblFoodStops.foodStopId ORDER BY tblFoodStops.name";
        return Database::executeSql($sql);
    }
    static public function getTotalItemsRecovered() : int {
        $sql = "SELECT sum(tblReservations.quantity) as total FROM tblReservations WHERE status = ? OR status = ? ";
        $results = Database::executeSql($sql, "ii", array(Reservation::$RESERVATION_STATUS_FULFILLED, Reservation::$RESERVATION_STATUS_ON_DEMAND));

        return $results[0]["total"] ?? 0;
    }

    static public function getItemsRecoveredLastWeek() : int {
        $sql = "SELECT sum(tblReservations.quantity) as total FROM tblReservations WHERE (status = ? OR status = ?) AND timeCreated > ? ";
        $lastWeekTimestamp = self::getLastWeekTimestamp();
        $results = Database::executeSql($sql, "iii", array(Reservation::$RESERVATION_STATUS_FULFILLED, Reservation::$RESERVATION_STATUS_ON_DEMAND, $lastWeekTimestamp));

        return $results[0]["total"] ?? 0;
    }

    static public function getItemsPerDay() {
        $sql = "SELECT DATE(FROM_UNIXTIME(timeCreated)) as createdDate,SUM(tblReservations.quantity) as numPerDate FROM tblReservations WHERE status = ? or status = ? GROUP BY DATE(FROM_UNIXTIME(timeCreated)) ORDER BY createdDate";
        $results = Database::executeSql($sql, "ii", array(Reservation::$RESERVATION_STATUS_FULFILLED, Reservation::$RESERVATION_STATUS_ON_DEMAND));
        return $results;
    }

    static public function getTotalItemsNotRecovered() {
        $sql = "
            SELECT SUM(
	        -- Take the quantity from the listing - those that were fulfilled  or picked up on demand 
	        tblListings.quantity - (SELECT COALESCE(SUM(tblReservations.quantity),0) FROM tblReservations WHERE listingId = tblListings.listingId AND (status = ? OR status = ?) )
                ) as total 
            -- Only include listings that were expired
            from tblListings WHERE expirationTime < ?";

        $results = Database::executeSql($sql, "iii", array(Reservation::$RESERVATION_STATUS_FULFILLED, Reservation::$RESERVATION_STATUS_ON_DEMAND, time()));
        return $results[0]["total"];
    }

}