<?php
require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");
require_once(__DIR__ . "/Filesystem.php");

/**
 * Class ListingsModel
 */
class ListingsModel {
    /**
     * Creates a listing
     * @param Listing $listing
     * @return bool true on success, false otherwise
     */
    static public function createListing(Listing $listing): bool {
        $sql = "INSERT INTO tblListings(userId, foodStopId, title, description, quantity, creationTime, expirationTime, weightOunces) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $id = Database::executeSql($sql, "iissiiii", array($listing->userId, $listing->foodStopId, $listing->title, $listing->description, $listing->quantity, $listing->creationTime, $listing->expirationTime, $listing->weightOunces));

        if (isset($listing->image)) {
            Filesystem::saveFile($id, base64_decode($listing->image));
        }

        return !isset(Database::$lastError);
    }

    /**
     * Updates a listing
     * @param Listing $listing
     * @return bool true on success, false otherwise
     */
    static public function updateListing(Listing $listing): bool {
        $sql = "UPDATE tblListings SET userId = ?, foodStopId = ?, title = ?, description = ?, quantity = ?, creationTime = ?, expirationTime = ?, weightOunces = ? WHERE listingId = ?";

        $id = Database::executeSql($sql, "iissiiiii", array($listing->userId, $listing->foodStopId, $listing->title, $listing->description, $listing->quantity, $listing->creationTime, $listing->expirationTime, $listing->weightOunces, $listing->listingId));
        return !isset(Database::$lastError);
    }

    /**
     * Gets all listings that have not expired
     * @return Listing[] Array contains array of Listing objects
     */
    static public function getListings(): array {
        $sql = "SELECT * from tblListings WHERE expirationTime > ? ORDER BY creationTime DESC";
        $results = Database::executeSql($sql, "i", array(time()));

        $listings = array();
        foreach ($results as $result) {
            array_push($listings, new Listing($result));
        }

        return $listings;
    }

    /**
     * Gets all listings that have not expired
     * @param int|null $foodStopId
     * @return Listing[] Array contains array of Listing objects
     */
    static public function getFoodStopListings(int $foodStopId ): array {
        $sql = "SELECT * from tblListings WHERE foodStopId = ? AND expirationTime > ? ORDER BY creationTime DESC ";
        $results = Database::executeSql($sql, "ii", array($foodStopId, time()));

        $listings = array();
        foreach ($results as $result) {
            array_push($listings, new Listing($result));
        }

        return $listings;
    }


    /**
     * Gets the listings that have recently expired from a food stop
     * @param int $foodStopId
     * @return array
     */
    static public function getRecentlyExpiredFoodStopListings(int $foodStopId, int $limitInHours): array {
        $sql = "SELECT * from tblListings WHERE foodStopId = ? AND expirationTime > ? AND expirationTime < ? ORDER BY creationTime DESC ";
        $startTime = time() - ($limitInHours * 60 * 60);
        $results = Database::executeSql($sql, "iii", array($foodStopId, $startTime, time()));

        $listings = array();
        foreach ($results as $result) {
            array_push($listings, new Listing($result));
        }

        return $listings;
    }


    /**
     * @param int $id The id of the single listing
     * @return Listing Returns back the listing if it exists, null otherwise
     */
    static public function getListing(int $id) {
        $sql = "SELECT * from tblListings where listingId = ?";
        $results = Database::executeSql($sql, "i", array($id));
        if (sizeof($results) > 0) {
            return new Listing($results[0]);
        }
        else {
            return null;
        }
    }

    /**
     * Returns the listing image
     * @param $id
     * @return string Returns the image data for a specific listing
     */
    static public function getListingImage($id): string {
        return Filesystem::getFile($id);
    }

    /**
     * Deletes a listing based on ID
     * @param $id
     * @return bool
     */
    static public function deleteListing($id): bool {
        $sql = "DELETE from tblListings where listingId = ?";
        Database::executeSql($sql, "i", array($id));
        return !isset(Database::$lastError);
    }
}