<?php
require_once(__DIR__ . "/../../lib/Security.php");
require_once(__DIR__ . "/../../lib/Session.php");
require_once(__DIR__ . "/../../controllers/UsersController.php");
require_once(__DIR__ . "/../../controllers/FoodStopsController.php");
require_once(__DIR__ . "/../../controllers/ReservationsController.php");
require_once(__DIR__ . "/../../controllers/ListingsController.php");
require_once(__DIR__ . "/../../model/types/FoodStop.php");
require_once(__DIR__ . "/../../model/types/User.php");
require_once(__DIR__ . "/../../model/types/Reservation.php");
require_once(__DIR__ . "/../../model/types/Listing.php");


if (!Session::isSessionValid()) {
  header('Location: '. "index.php");
}

$selectedFoodStopId = $_GET["foodstop"];
$selectedFoodStop = null;

$action = $_GET["action"];
// Based on the action, fullfill reservations, update listings, or delete listings
switch ($action) {
  case "retrieve":
      $reservation = new Reservation($_GET);
      $fulfillRequest = new Request($reservation, $selectedFoodStopId, "fulfill", Session::getSessionUserId());
      $response = ReservationsController::patch($fulfillRequest);
      header("Location: " . "dashboard.php?foodstop=$selectedFoodStopId");
      die();
      break;
}

$baseRequest = new Request(null, null, null, Session::getSessionUserId());

$foodStops = FoodStopsController::get($baseRequest)->data;
$user = new User(UsersController::get(new Request($baseRequest))->data);


// Default to first food stop
if (isset($selectedFoodStopId)) {
    foreach($foodStops as $foodStop) {
        $foodStop = new FoodStop($foodStop);
        if ($foodStop->foodStopId == $selectedFoodStopId) {
            $selectedFoodStop = $foodStop;
        }
    }
}
else {
    $selectedFoodStop = new FoodStop($foodStops[0]);
}
$reservationRequest = new Request(null, $selectedFoodStop->foodStopId, "foodstop", Session::getSessionUserId());
$listingRequest = new Request(null, $selectedFoodStop->foodStopId, "foodstop", Session::getSessionUserId());

$reservations = ReservationsController::get($reservationRequest)->data;
$listings = ListingsController::get($listingRequest)->data;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js" integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0" crossorigin="anonymous"></script>

  <script src="js/main.js"></script>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/main.css">
  <title>CampusPlate | Manage</title>
</head>
<body>

<div class="container min-vh-100 h-100" id="login">
  <h1 class="display-4"> Dashboard</h1>
  <h5 class="subtitle" style="color:#<?=$selectedFoodStop->hexColor?>"><span class="badge" style="background-color:#<?=$selectedFoodStop->hexColor?>; border-radius:50%"><?=$selectedFoodStop->foodStopNumber?></span> <?=$selectedFoodStop->name?> <div class="float-end">
              <button class="btn btn-primary" onclick="window.location='dashboard.php?foodstop=<?=$selectedFoodStop->foodStopId?>'"><span class="glyphicon glyphicon-refresh"></span><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                  <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
              </svg> Update</button>
      </div></h5>


    <hr />
  <!-- Modal -->
  <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Confirm Retrieval Amount</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Confirm the amount of items that were retrieved.</p>
          <p><em>If full amount was not retrieved, remaining items will become available.</em></p>
          <select class="form-select" id="selectedAmount">
            <option value="4" selected>All</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1</option>
          </select>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="submit">Submit</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    var reservations = [];
    var confirmModal = document.getElementById('confirmModal')

    var modalReservationId;

    function getReservation(id) {
        var reservation = null;

        for(var i = 0; i < reservations.length; i++) {
            reservation = reservations[i];
            if (reservation.reservationId === parseInt(id)) {
                break;
            }
        }
        return reservation;
    }

    confirmModal.querySelector("#submit").addEventListener("click", function(event) {
        let reservation = getReservation(modalReservationId);
        let quantityRetrieved = confirmModal.querySelector("#selectedAmount").value;
        window.location='dashboard.php?foodstop=<?=$selectedFoodStop->foodStopId?>&action=retrieve&reservationId=' + reservation.reservationId + '&quantity=' + quantityRetrieved;
       //console.log("Retrieving " + reservation.reservationId + " quantity of " + quantityRetrieved);
    });

    confirmModal.addEventListener('show.bs.modal', function (event) {
      let sourceButton = event.relatedTarget;

      modalReservationId = sourceButton.getAttribute("data-bs-id");
      let reservation = getReservation(modalReservationId);

      //TODO: Check if reservation is null
      let quantity = reservation.quantity;

      confirmModal.querySelector("#selectedAmount").innerHTML = "";
      for(var x = quantity; x > 0; x--) {
        var select = document.createElement("option");
        select.value = x;
        select.innerText = x;
        confirmModal.querySelector("#selectedAmount").appendChild(select);
      }
    });

  </script>

  <h3>Active Reservations</h3>

  <table class="table table-sm table-hover table-responsive-lg">
    <thead>
    <tr>
      <th scope="col">Food Listing Title</th>
      <th scope="col">Pickup Expiration</th>
      <th scope="col">Quantity</th>
      <th scope="col">Code</th>
      <th scope="col">Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php
        if (sizeof($reservations) > 0) {
            foreach($reservations as $reservation) {
                $reservation = new Reservation($reservation);

                ?>
                <script>
                    reservation = <?=json_encode($reservation)?>;
                    reservations.push(reservation);
                </script>
                    <?php

                $matchedListing = new Listing();
                foreach($listings as $listing) {
                    $listing = new Listing($listing);
                    if ($listing->listingId == $reservation->listingId) {
                        $matchedListing = $listing;
                    }
                }
    ?>
                <tr>
                    <th scope="row"><?=$matchedListing->title?></th>
                    <td><?=date("g:ia", $reservation->timeExpired)?></td>
                    <td><?=$reservation->quantity?></td>
                    <td><?=$reservation->code?></td>
                    <td>
                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal" data-bs-id="<?=$reservation->reservationId?>">Retrieved</button>
                    </td>
                </tr>
    <?php
            }
        }
        else {
    ?>
    <?php
        }
    ?>

    </tbody>
  </table>
  <div style="height:50px"></div>

  <h3>Food Listings</h3>
  <table class="table table-sm table-hover">
    <thead>
    <tr>
      <th scope="col">Title</th>
      <th scope="col">Description</th>
      <th scope="col">Quantity Remaining</th>
      <th scope="col">Date Added</th>
      <th scope="col">Date Expires</th>
      <th scope="col">Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php
        foreach($listings as $listing) {
            $listing = new Listing($listing);

    ?>
            <tr>
                <th scope="row"><?=$listing->title?></th>
                <td><?=$listing->description?></td>
                <td><?=$listing->quantityRemaining?>/<?=$listing->quantity?></td>
                <td><?=date("M jS g:ia", $listing->creationTime)?></td>
                <td><?=date("M jS g:ia", $listing->creationTime + (60 * 60 * 48))?></td>
                <td>
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal" data-bs-id="4">Edit</button>
                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmModal" data-bs-id="4">Delete</button>
                </td>
            </tr>
    <?php
        }
    ?>
    </tbody>
  </table>
</div>
</body>
</html>
