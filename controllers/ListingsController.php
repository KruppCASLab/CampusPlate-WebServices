<?php

require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/Request.php");
require_once(__DIR__ . "/../model/types/Listing.php");
require_once(__DIR__ . "/../model/ListingsModel.php");
require_once(__DIR__ . "/../lib/Geofence.php");
require_once(__DIR__ . "/../model/AuthorizationModel.php");

class ListingsController {
  /**
   * Returns all listings or the image of a listing if the ID and param are set
   * @param Request $request
   * @return Response data contains array of listings or image of listing
   */
  static public function get(Request $request) : Response {
    $id = $request->id;
    $param = $request->param;

    if (isset($id) && isset($param)) {
      $data = ListingsModel::getListingImage($id);
      return new Response($data);
    }
    else {
      return new Response(ListingsModel::getListings());
    }
  }

  /**
   * Allows the creation of a food listing
   * @param Request $request
   * @return Response status code contains success
   */
  static public function post(Request $request) : Response {
    $listing = new Listing($request->data);
    $listing->userId = $request->userId;
    $status = 1;

    if (AuthorizationModel::isFoodStopManager($request->userId, $listing->foodStopId) || AuthorizationModel::isAdmin($request->userId)) {
      if (ListingsModel::createListing($listing)) {
        $status = 0;
      }
    }
    else {
      // Return Unauthorized
      $status = 401;
    }
    return new Response(null, null, $status);
  }

  /**
   * Allows the update of a quantity of a food listing
   * @param Request $request
   * @return Response
   */
  static public function patch(Request $request) : Response {
    $id = $request->id;
    foreach($request->data as $key=>$val) {
      if ($key == "quantity") {
        ListingsModel::updateQuantity($id, $val);
        return new Response();
      }
    }
    return new Response();
  }
}
