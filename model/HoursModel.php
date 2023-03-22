<?php
require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/UsersModel.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/FoodStop.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");
require_once(__DIR__ . "/Filesystem.php");
require_once (__DIR__ . "/types/Hours.php");

class HoursModel
{

    //Delete any old records that correspond to the same FoodStop and DayOfWeek

    /**
     * Sets a new entry in the Hours Table
     * @param Hours[] $hours
     * @return bool
     */
    static public function setHours(array $hours): bool
    {
        foreach($hours as $newhours) {
            $sql = "INSERT INTO  tblFoodStopHours(foodStopId, dayOfWeek, timeOpen, timeClose) VALUES (?, ?, ?, ?)";
            Database::executeSql($sql, "isss", array($newhours->foodStopId, $newhours->dayOfWeek, $newhours->timeOpen, $newhours->timeClose));

        }
        return !isset(Database::$lastError);
    }

    /**
     * Returns all the hours for a given food stop
     * @param $foodStopId
     * @return array
     */
    static public function getFoodStopHours($foodStopID): array
    {
        $sql = "SELECT * FROM tblFoodStopHours WHERE foodStopId = ?";
        $results = Database::executeSql($sql, "i", array($foodStopID));
        $hours = array();
        foreach ($results as $result) {
            array_push($hours, new Hours($result));
        }

        return $hours;
    }

    /**
     * Removes all food stop hours with a certain foodStopId
     * @param $foodStopId
     * @return bool
     */
    static public function removeFoodStopHours($foodStopId): bool
    {
        $sql = "DELETE FROM tblFoodStopHours WHERE foodStopId = ?";
        $results = Database::executeSql($sql, "i", array($foodStopId));

        return !isset(Database::$lastError);
    }

}
