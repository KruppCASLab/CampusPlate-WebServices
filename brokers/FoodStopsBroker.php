<?php
require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/FoodStop.php");
require_once(__DIR__ . "/../model/FoodStopsModel.php");

class FoodStopsBroker {
  /**
   * Allows the creation of a food stop
   * @param $requestData
   * @return Response
   */
  static public function post($requestData) : Response {
    $foodStop = new FoodStop($requestData[0]);

    $status = 1;
    if (FoodStopsModel::createFoodStop($foodStop)) {
      $status = 0;
    }
    return new Response(null, null, $status);
  }


  /**
   * @param $requestData
   * @return Response Array of Food Stops
   */
  static public function get($requestData) : Response {
    $id = $requestData[0];

    // TODO: Support getting 1 listing detail
    $foodstops = FoodStopsModel::getFoodStops();

    return new Response($foodstops);
  }

}