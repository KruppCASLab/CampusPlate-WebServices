<?php

require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/Listing.php");
require_once(__DIR__ . "/../model/ListingsModel.php");
require_once(__DIR__ . "/../lib/Geofence.php");

class ListingsBroker {
  /**
   * Returns all listings or a subset based on ID
   * @param $requestData
   * @return Response data contains array of listings
   */
  static public function get($requestData) : Response {
    $id = $requestData[0];

    // TODO: Support getting 1 listing detail
    $listings = ListingsModel::getListings();

    return new Response($listings);
  }

  /**
   * Allows the creation of a food listing
   * @param $requestData
   * @return Response status code contains success
   */
  static public function post($requestData) : Response {
    $listing = new Listing($requestData[0]);

    //TODO: GeoFencing is broken, need to fix
    $status = 1;
    if (ListingsModel::createListing($listing)) {
      $status = 0;
    }
    return new Response(null, null, $status);
  }

  /**
   * Allows the update of a quantity of a food listing
   * @param $requestData
   * @return Response
   */
  static public function patch($requestData) : Response {
    $id = $requestData[0];
    foreach($requestData[1] as $key=>$val) {
      if ($key == "quantity") {
        ListingsModel::updateQuantity($id, $val);
        return new Response();
      }
      else if ($key == "image") {
        // TODO: Allow store of image
      }
    }
    return new Response();
  }
}
