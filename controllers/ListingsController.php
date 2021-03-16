<?php

require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/Request.php");
require_once(__DIR__ . "/../model/types/Listing.php");
require_once(__DIR__ . "/../model/ListingsModel.php");
require_once(__DIR__ . "/../model/ReservationsModel.php");
require_once(__DIR__ . "/../lib/Geofence.php");
require_once(__DIR__ . "/../model/AuthorizationModel.php");

abstract class ListingsResponseCode {
    const Unauthorized = 401;
    const DeleteFailReservationsFulfilled = 1;
}

class ListingsController {
    /**
     * Returns all listings or the image of a listing if the ID and param are set
     * @param Request $request
     * @return Response data contains array of listings or image of listing
     */
    static public function get(Request $request): Response {
        $id = $request->id;
        $param = $request->param;

        if (isset($id) && $param == "image") {
            $data = ListingsModel::getListingImage($id);
            return new Response(base64_encode($data));
        }
        else if (isset($id) && $param == "foodstop") {
            if (AuthorizationModel::isFoodStopManager($request->userId, $id) || AuthorizationModel::isAdmin($request->userId)) {
                $listings = ListingsModel::getListings($id);
            }
            else {
                return new Response(null, null, 1);
            }
        }
        else if (isset($id)) {
            $listing = ListingsModel::getListing($id);

            if (AuthorizationModel::isFoodStopManager($request->userId, $listing->foodStopId) || AuthorizationModel::isAdmin($request->userId)) {
                return new Response($listing);
            }
            else {
                return new Response(null, null, 1);
            }
        }
        else {
            $listings = ListingsModel::getListings();
        }

        foreach ($listings as $listing) {
            $listing->quantityRemaining = ($listing->quantity - ReservationsModel::getReservationQuantity($listing->listingId));
        }
        return new Response($listings);
    }

    /**
     * Allows the creation of a food listing
     * @param Request $request
     * @return Response status code contains success
     */
    static public function post(Request $request): Response {
        $listing = new Listing($request->data);
        $listing->userId = $request->userId;

        if (!isset($listing->creationTime)) {
            $listing->creationTime = time();
        }
        if (!isset($listing->expirationTime)) {
            $listing->expirationTime = time() + (60 * 60 * 24 * 2); // 2 Days expire by default
        }

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
     * Allows the update of a food listing
     * @param Request $request
     * @return Response status code contains success
     */
    static public function put(Request $request): Response {
        $listing = new Listing($request->data);
        $listing->userId = $request->userId;

        if (!isset($listing->creationTime)) {
            $listing->creationTime = time();
        }
        if (!isset($listing->expirationTime)) {
            $listing->expirationTime = time() + (60 * 60 * 24 * 2); // 2 Days expire by default
        }

        $status = 1;

        if (AuthorizationModel::isFoodStopManager($request->userId, $listing->foodStopId) || AuthorizationModel::isAdmin($request->userId)) {
            if (ListingsModel::updateListing($listing)) {
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
     * Deletes a listing as long as there are not fulfilled reservations
     * @param Request $request
     * @return Response
     */
    static public function delete(Request $request): Response {
        $listing = new Listing($request->data);
        $listingId = $listing->listingId;

        if (!(AuthorizationModel::isFoodStopManager($request->userId, $request->id) || AuthorizationModel::isAdmin($request->userId))) {
            return new Response(null, null, ListingsResponseCode::Unauthorized);
        }

        // Check if we have any fulfilled reservations, if so, then we can't delete the listing
        $fulfilledReservations = ReservationsModel::getFulfilledReservations($listingId);
        if (sizeof($fulfilledReservations) > 0) {
            return new Response(null, null, ListingsResponseCode::DeleteFailReservationsFulfilled);
        }

        // Otherwise, we don't have fulfilled reservations, we can delete the listing and any pending reservations
        if (!ListingsModel::deleteListing($listingId)) {
            return new Response(null, null, 2);
        }
        if (!ReservationsModel::deleteReservationsFromListing($listingId)) {
            return new Response(null, null, 3);
        }

        return new Response(null, null, 0);
    }

}
