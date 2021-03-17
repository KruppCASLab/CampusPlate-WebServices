<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/FoodStop.php");
require_once(__DIR__ . "/types/User.php");

class AuthorizationModel {
    static $adminRoleId = 1;

    static public function isFoodStopManager(int $userId, int $foodStopId): bool {
        $sql = "SELECT * from tblFoodStopManagers WHERE userId = ? AND foodStopId = ?  ";
        $results = Database::executeSql($sql, "ii", array($userId, $foodStopId));
        return sizeof($results) > 0;
    }

    static public function isAdmin(int $userId): bool {
        $sql = "SELECT * from tblUsers WHERE userId = ? AND role = ?";
        $results = Database::executeSql($sql, "ii", array($userId, self::$adminRoleId));
        return sizeof($results) > 0;
    }
}