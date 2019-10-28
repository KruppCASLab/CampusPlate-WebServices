<?php
require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");

class ListingsModel {

  static public function createListing(Listing $listing) : Response{
    $db = new Database();

    $sql = "INSERT INTO tblListings(userId, title, locationDescription, lat, lng, quantity, creationTime) VALUES (?, ?, ?, ?, ?, ?, ?)";

    $db->executeSql($sql, "issddii", array($listing->userId, $listing->title, $listing->locationDescription, $listing->lat, $listing->lng, $listing->quantity, time()));

    return new Response(null, $db->lastError);
  }

  static public function getListings() : Response {
    $db = new Database();

    $sql = "SELECT * from tblListings ORDER BY creationTime DESC";

    $results = $db->executeSql($sql);
    $objectresults = array();
    foreach($results as $result) {
      array_push($objectresults, new Listing($result));
    }

    return new Response($objectresults, $db->lastError);
  }

}