<?php
require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/UsersModel.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/FoodStop.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");
require_once(__DIR__ . "/Filesystem.php");

class FoodStopsModel {

    /**
     * Creates a food stop
     * @param FoodStop $foodStop
     * @return bool true on success, false, on error
     */
    static public function createFoodStop(FoodStop $foodStop): bool {
        $sql = "INSERT INTO tblFoodStops(name, description, lat, lng, streetAddress, hexColor, foodStopNumber, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        Database::executeSql($sql, "ssddssis", array($foodStop->name, $foodStop->description, $foodStop->lat, $foodStop->lng, $foodStop->streetAddress, $foodStop->hexColor, $foodStop->foodStopNumber, $foodStop->type));

        return !isset(Database::$lastError);
    }


    /**
     * Returns all food stops
     * @return FoodStop[]
     */
    static public function getFoodStops(): array {
        $sql = "SELECT * from tblFoodStops ORDER BY foodStopId ASC";
        $results = Database::executeSql($sql);
        $foodstops = array();
        foreach ($results as $result) {
            array_push($foodstops, new FoodStop($result));
        }

        return $foodstops;
    }


    /**
     * Returns food stops that a particular user manages
     * @param $userId
     * @return array
     */
    static public function getManagedFoodStops($userId): array {
        $sql = "SELECT * from tblFoodStops WHERE foodStopId IN (SELECT foodStopId FROM tblFoodStopManagers WHERE userId = ?)";
        $results = Database::executeSql($sql, "i", array($userId));
        $foodstops = array();
        foreach ($results as $result) {
            array_push($foodstops, new FoodStop($result));
        }
        return $foodstops;
    }

    static public function removeUserFromFoodStopManagerRole($userId, $foodStopId) {
        $sql = "DELETE FROM tblFoodStopManagers WHERE userId = ? AND foodStopId = ?";
        Database::executeSql($sql, "ii", array($userId, $foodStopId));
    }

    static public function addUserToFoodStopManagerRole($email, $foodStopId) : bool {
        $userId = UsersModel::getUserId($email);

        // Check if the user did not exist, if not, return false
        if ($userId == -1) {
            return false;
        }

        // Check if the user is already a food stop manager before adding them
        $sql = "SELECT userId from tblFoodStopManagers WHERE userId = ? AND foodStopId = ?";
        $results = Database::executeSql($sql, "ii", array($userId, $foodStopId));
        if (sizeof($results) == 0) {
            // Add them since they are not
            $sql = "INSERT INTO tblFoodStopManagers (userId, foodStopId) VALUES (?, ?)";
            Database::executeSql($sql, "ii", array($userId, $foodStopId));
        }
        return true;
    }

}