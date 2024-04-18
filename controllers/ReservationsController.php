<?php
require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/Request.php");
require_once(__DIR__ . "/../model/types/Listing.php");
require_once(__DIR__ . "/../model/types/Reservation.php");
require_once(__DIR__ . "/../model/ListingsModel.php");
require_once(__DIR__ . "/../model/ReservationsModel.php");
require_once(__DIR__ . "/../model/AuthorizationModel.php");

class ReservationsController {
    static public function post(Request $request): Response {
        $reservation = new Reservation($request->data);

        
        $reservation->userId = $request->userId;


        // By default, we are trying to place the reservation, so set the status to placed. Otherwise, it could be an
        // on demand reservation
        if (!isset($reservation->status)) {
            $reservation->status = Reservation::$RESERVATION_STATUS_PLACED;
        }
        
        // Check if listing exists
        $listing = ListingsModel::getListing($reservation->listingId);

        if ($listing === null) {
            return new Response(null, null, Reservation::$RESERVATION_RETURN_CODE_LISTING_NOT_AVAILABLE);
        }


        // Check if there is enough quantity to be reserved
        $totalReserved = ReservationsModel::getReservationQuantity($reservation->listingId);

        // Check to make sure enough quantity is available
        if (isset($totalReserved) && (($totalReserved + $reservation->quantity) > $listing->quantity)) {
            return new Response(null, null, Reservation::$RESERVATION_RETURN_CODE_QUANTITY_NOT_AVAILABLE);
        }

        // Create random code
        $reservationCode = Security::getRandomPin();

        // TODO: Check if this code exists already for this listing
        $reservation->code = $reservationCode;
        $reservation->timeCreated = time();

        $minuteExpire = Config::getConfigValue("food", "reservation_expire");
        if (!isset($minuteExpire)) {
            $minuteExpire = 30;
        }
        $reservation->timeExpired = time() + ($minuteExpire * 60); // 30 minutes,

        $foodStop = FoodStopsModel::getFoodStop($listing->foodStopId);
        $stopType = $foodStop->type;

        if ($stopType == "managed") {
            ReservationsModel::createReservation($reservation);
        }
        elseif ($stopType == "unmanaged") {
            $reservation->status = Reservation::$RESERVATION_STATUS_RETRIEVAL;
            $testThreshold = 0.0004;
            $lat = $foodStop->lat;
            $lng = $foodStop->lng;

            $userLat = $reservation->lat;
            $userLng = $reservation->lng;
            if (($userLat < ($lat + $testThreshold)) && ($userLat > ($lat - $testThreshold)) &&
                ($userLng < ($lng + $testThreshold)) && ($userLng > ($lng - $testThreshold))) {
                ReservationsModel::createReservation($reservation);
            }
            else {
                $reservation->status = Reservation::$RETRIEVAL_RETURN_CODE_OUT_OF_RANGE;
            };
        }
        return new Response($reservation);
    }

    static public function get(Request $request): Response {
        if ($request->param == "foodstop" && isset($request->id)) {
            if (AuthorizationModel::isFoodStopManager($request->userId, $request->id) || AuthorizationModel::isAdmin($request->userId)) {
                return new Response(ReservationsModel::getFoodStopReservations($request->id));
            }
            else {
                return new Response(null, null, 1);
            }
        }
        else {
            return new Response(ReservationsModel::getUserReservations($request->userId));
        }

    }

    static public function patch(Request $request): Response {
        if ($request->param == "fulfill" && isset($request->id)) {
            if (AuthorizationModel::isFoodStopManager($request->userId, $request->id) || AuthorizationModel::isAdmin($request->userId)) {
                $reservation = new Reservation($request->data);
                return new Response(ReservationsModel::fulfillReservation($reservation));
            }
            else {
                return new Response(null, null, 1);
            }
        }
        else {
            return new Response(null, null, 2);
        }
    }

    static public function delete(Request $request): Response {
        $reservationId = $request->id;
        $userId = $request->userId;

        $success = ReservationsModel::deleteReservationForUser($reservationId, $userId);
        if ($success) {
            return new Response(null, null, 0);
        }
        else {
            return new Response(null, null, 1);
        }
    }

}