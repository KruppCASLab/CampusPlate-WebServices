<?php
require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");
require_once(__DIR__ . "/Filesystem.php");

class ListingsModel {
  /**
   * Creates a listing
   * @param Listing $listing
   * @return bool true on success, false otherwise
   */
  static public function createListing(Listing $listing) : bool {
    $sql = "INSERT INTO tblListings(userId, foodStopId, title, description, quantity, creationTime) VALUES (?, ?, ?, ?, ?, ?)";
    $id = Database::executeSql($sql, "iissii", array($listing->userId, $listing->foodStopId, $listing->title, $listing->description, $listing->quantity, $listing->creationTime));

    if (isset($listing->image)) {
      Filesystem::saveFile($id, base64_decode($listing->image));
    }

    return ! isset(Database::$lastError);
  }

  /**
   * Returns all listings ordered by creation time in descending order
   * @return Listing[] Array contains array of Listing objects
   */
  static public function getListings() : array {
    $sql = "SELECT * from tblListings ORDER BY creationTime DESC";
    $results = Database::executeSql($sql);
    $listings = array();
    foreach($results as $result) {
      array_push($listings, new Listing($result));
    }

    return $listings;
  }


  /**
   * @param int $id The id of the single listing
   * @return Listing Returns back the listing if it exists, null otherwise
   */
  static public function getListing(int $id)  {
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
   * @param $id
   * @return string Returns the image data for a specific listing
   */
  static public function getListingImage($id) : string {
    return Filesystem::getFile($id);
  }


}