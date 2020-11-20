<?php
require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/FoodStop.php");
require_once(__DIR__ . "/../model/FoodStopsModel.php");

class FoodStopsBroker {
  /**
   * Allows the creation of a food stop
   * @param $requestData Data containing Food Stop
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

}