<?php

require_once(__DIR__ . "/../model/types/Listing.php");
require_once(__DIR__ . "/../model/ListingsModel.php");
require_once(__DIR__ . "/../lib/Geofence.php");

class ListingsBroker {
  static public function get($requestData) {
    $id = $requestData[0];
    if ($id == "") {
      return ListingsModel::getListings();
    } else {
      return ListingsModel::getListings();
    }
  }

  static public function post($requestData) {
    $listing = new Listing($requestData[0]);

    //TODO: GeoFencing is broken, need to fix
    //if (GeoFence::onCampus($listing->lat, $listing->lng)) {
      return ListingsModel::createListing($listing);
    //}
  }

  //TODO: Complete for update
  static public function put($requestData) {
    $id = $requestData[0];
  }

  //TODO: Complete for delete
  static public function delete($requestData) {
    $id = $requestData[0];
  }
}

?>

