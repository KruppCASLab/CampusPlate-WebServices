<?php
require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/Request.php");
require_once(__DIR__ . "/../model/types/Hours.php");
require_once(__DIR__ . "/../model/HoursModel.php");
require_once(__DIR__ . "/../model/AuthorizationModel.php");
class HoursController
{
    static public function post(Request $request): Response {
        $hours = array();
        foreach ($request->data as $newhours) {
            array_push($hours, new Hours($newhours));
        }

        $checkId = $hours[0]->foodStopId;

        $status = 1;
        if (AuthorizationModel::isAdmin($request->userId)) {
            if (HoursModel::removeFoodStopHours($checkId)) {
                if (HoursModel::setHours($hours)) {
                    $status = 0;
                }
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
    static public function get(Request $request): Response {

        $foodstophours = HoursModel::getFoodStopHours($request->data->foodStopId);

        return new Response($foodstophours);
    }
}