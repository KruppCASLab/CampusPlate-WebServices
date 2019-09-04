<?php
require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");

class ListingsModel {

  static public function createListing(Listing $listing) {
    $db = new Database();

    $sql = "INSERT INTO tblListings(userId, title, lat, lng, quantity) VALUES (?, ?, ?, ?, ?)";

    $db->executeSql($sql, "isddi", $listing->userId, $listing->title, $listing->lat, $listing->lng, $listing->quantity);

    return $db->lastError;
  }

  static public function getListings() : array {
    $db = new Database();

    $sql = "SELECT * from tblListings";

    $results = $db->executeSql($sql);
    $objectresults = array();
    foreach($results as $result) {
      array_push($objectresults, new Listing($result));
    }

    return $objectresults;
  }

}