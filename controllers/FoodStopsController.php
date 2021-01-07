<?php
require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/Request.php");
require_once(__DIR__ . "/../model/types/FoodStop.php");
require_once(__DIR__ . "/../model/FoodStopsModel.php");

class FoodStopsController {
  /**
   * Allows the creation of a food stop
   * @param Request $request
   * @return Response
   */
  static public function post(Request $request) : Response {
    $foodStop = new FoodStop($request->data);

    $status = 1;
    if (FoodStopsModel::createFoodStop($foodStop)) {
      $status = 0;
    }
    return new Response(null, null, $status);
  }


  /**
   * Gets the food stops that are available
   * @param Request $request The
   * @return Response Array of Food Stops
   */
  static public function get(Request $request) : Response {
    $foodstops = FoodStopsModel::getFoodStops();

    return new Response($foodstops);
  }

}