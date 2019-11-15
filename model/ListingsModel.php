<?php
require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/DBResponse.php");

class ListingsModel {

  static public function createListing(Listing $listing) : DBResponse{
    $db = new Database();

    $sql = "INSERT INTO tblListings(userId, title, locationDescription, lat, lng, quantity, creationTime, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $db->executeSql($sql, "issddiib", array($listing->userId, $listing->title, $listing->locationDescription, $listing->lat, $listing->lng, $listing->quantity, time(), $listing->image));

    return new DBResponse(null, $db->lastError);
  }

  static public function getListings() : DBResponse {
    $db = new Database();

    $sql = "SELECT * from tblListings ORDER BY creationTime DESC";

    $results = $db->executeSql($sql);
    $objectresults = array();
    foreach($results as $result) {
      array_push($objectresults, new Listing($result));
    }

    return new DBResponse($objectresults, $db->lastError);
  }

  static public function getListing($id) : DBResponse {
    $db = new Database();

    $sql = "SELECT * from tblListings WHERE listingId = ?";

    $results = $db->executeSql($sql, "i", array($id));

    return new DBResponse($results[0], $db->lastError);
  }

  static public function updateQuantity($id, $quantityChange) : DBResponse {
    $db = new Database();

    // This SQL looks wild because of the nested select, but this gets around MySQLs restriction of updating the table
    // while selecting from it, when we do the select quantity, that places it in a temporary varaible
    $sql = "UPDATE tblListings set quantity = ((select quantity from (select quantity from tblListings where listingId = ?) AS quantity) + ?) where listingId = ?";
    $db->executeSql($sql, "iii", array($id, $quantityChange, $id));

    $sql = "DELETE FROM tblListings where quantity <= 0";
    $db->executeSql($sql);

    return new DBResponse(null, $db->lastError);
  }

}