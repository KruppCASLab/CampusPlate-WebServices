<?php
require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/Request.php");
require_once(__DIR__ . "/../model/types/FoodStop.php");
require_once(__DIR__ . "/../model/FoodStopsModel.php");
require_once(__DIR__ . "/../model/AuthorizationModel.php");

class FoodStopsController {
  /**
   * Allows the creation of a food stop
   * @param Request $request
   * @return Response
   */
  static public function post(Request $request) : Response {
    $foodStop = new FoodStop($request->data);

    $status = 1;
    if (AuthorizationModel::isAdmin($request->userId)) {
      if (FoodStopsModel::createFoodStop($foodStop)) {
        $status = 0;
      }
    }
    else {
      $status = 401;
    }
    return new Response(null, null, $status);
  }


  /**
   * Gets the food stops that are available
   * @param Request $request The
   * @return Response Array of Food Stops
   */
  static public function get(Request $request) : Response {
    $allfoodstops = FoodStopsModel::getFoodStops();
    $foodstops = array();
    if ($request->id == "manage") {
      // Return all food stops if the user is an admin
      if (AuthorizationModel::isAdmin($request->userId)) {
        $foodstops = $allfoodstops;
      }
      else {
        // Get each that the user is a manager of
        foreach ($allfoodstops as $foodstop) {
          if (AuthorizationModel::isFoodStopManager($request->userId, $foodstop->foodStopId)) {
            array_push($foodstops, $foodstop);
          }
        }
      }
    }
    else {
      $foodstops = $allfoodstops;
    }

    return new Response($foodstops);
  }

}