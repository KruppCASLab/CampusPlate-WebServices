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
    header('Location: ' . "index.php");
}

$selectedFoodStopId = $_GET["foodstop"];
$selectedFoodStop = null;

$errorOccurred = false;
$errorTitle = "";
$errorMainDescription = "";
$errorSubDescription = "";

$action = $_GET["action"];
// Based on the action, fullfill reservations, update listings, or delete listings
switch ($action) {
    case "ping":
        $response["time"] = time();
        die(json_encode($response));
    case "delete":
        $listingId = $_GET["listingId"];
        $listing = new Listing();
        $listing->listingId = $listingId;

        $deleteRequest = new Request($listing, $selectedFoodStopId, null, Session::getSessionUserId());
        $response = ListingsController::delete($deleteRequest);
        if ($response->status == ListingsResponseCode::DeleteFailReservationsFulfilled) {
            $errorOccurred = true;
            $errorTitle = "Unable to Delete Listing - Reservations Fulfilled";
            $errorMainDescription = "The food listing cannot be deleted as there have been fulfilled reservations for this food listing.";
            $errorSubDescription = "The information for this listing is needed to report on the food that is recovered. Only food listings that have not had food recovered can be deleted.";
        }
        else {
            header("Location: " . "dashboard.php?foodstop=$selectedFoodStopId");
            die();
        }
        break;
    case "retrieve":
        $reservation = new Reservation($_GET);
        $fulfillRequest = new Request($reservation, $selectedFoodStopId, "fulfill", Session::getSessionUserId());
        $response = ReservationsController::patch($fulfillRequest);
        header("Location: " . "dashboard.php?foodstop=$selectedFoodStopId");
        die();
    case "place":
        $reservation = new Reservation();
        $reservation->listingId = $_GET["listingId"];
        $reservation->quantity = $_GET["quantity"];
        $reservation->status = Reservation::$RESERVATION_STATUS_ON_DEMAND;

        $placeRequest = new Request($reservation, $selectedFoodStop, null, Session::getSessionUserId());
        $response = ReservationsController::post($placeRequest);

        if ($response->status == Reservation::$RESERVATION_RETURN_CODE_QUANTITY_NOT_AVAILABLE) {
            $errorOccurred = true;

            $errorTitle = "Unable to Complete Order - Quantity Changed";
            $errorMainDescription = "A reservation may have been placed for the selected food listing causing the quantity to change when the order was placed.
            Before placing an order, please click on the update button to show an updated quantity for each food listing.";
            $errorSubDescription = "If an item is popular, it is also possible that a reservation was placed right before the order was placed.";
        }
        else {
            header("Location: " . "dashboard.php?foodstop=$selectedFoodStopId");
            die();
        }
        break;
}

$baseRequest = new Request(null, null, null, Session::getSessionUserId());

$foodStops = FoodStopsController::get($baseRequest)->data;
$foodStopsManaged = FoodStopsController::get(new Request(null, null, "manage", Session::getSessionUserId()))->data;
$currentUser = new User(UsersController::get(new Request($baseRequest))->data);


// Default to first food stop
if (isset($selectedFoodStopId)) {
    foreach ($foodStopsManaged as $foodStop) {
        $foodStop = new FoodStop($foodStop);
        if ($foodStop->foodStopId == $selectedFoodStopId) {
            $selectedFoodStop = $foodStop;
        }
    }
}
else {
    $selectedFoodStop = new FoodStop($foodStopsManaged[0]);
    $selectedFoodStopId = $selectedFoodStop->foodStopId;
}
$reservationRequest = new Request(null, $selectedFoodStop->foodStopId, "foodstop", Session::getSessionUserId());
$listingRequest = new Request(null, $selectedFoodStop->foodStopId, "foodstop", Session::getSessionUserId());

$reservations = ReservationsController::get($reservationRequest)->data;
$listings = ListingsController::get($listingRequest)->data;

$recentlyExpiredListingRequest = new Request(null, $selectedFoodStop->foodStopId, "foodstoprecentlyexpired", Session::getSessionUserId());
$recentlyExpiredListings = ListingsController::get($recentlyExpiredListingRequest)->data;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0"
            crossorigin="anonymous"></script>

    <script src="js/main.js"></script>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <title>CampusPlate | Manage</title>
    <script>
        function changeFoodStop(foodStopId) {
            window.location = 'dashboard.php?foodstop=' + foodStopId;
        }

        function checkListings() {
            fetch('dashboard.php?action=ping')
                .then(response => response.json())
                .then(data => console.log(data))
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Keep food manager session alive
        window.setInterval(checkListings, 10000);
    </script>
</head>
<body>

<div class="container min-vh-100 h-100" id="login">
    <?php
    if (sizeof($foodStopsManaged) == 0) {
        die("Error: You are currently not authorized as a manager of food stops.");
    }
    if (AuthorizationModel::isAdmin(Session::getSessionUserId()) || AuthorizationModel::isAFoodStopManager(Session::getSessionUserId())) {
    ?>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="reporting.php">Reporting</a>
        </li>
        <li class="nav-item ">
            <a class="nav-link disabled" href="#">Users</a>
        </li>
    </ul>
    <?php
    }
    // Only show the selector if there are multiple food stops that are managed
    if (sizeof($foodStopsManaged) > 1) {
        ?>
        <div class="row">
            <div class="col-12">
                <div class="float-lg-end">
                    <span class="fw-lighter">Change Food Stop:</span>
                    <select class="form-select-sm w-auto d-inline-block fw-lighter" onchange="changeFoodStop(this.value)">
                        <?php
                        foreach ($foodStopsManaged as $foodStop) {
                            $foodStop = new FoodStop($foodStop);
                            ?>
                            <option <?= ($selectedFoodStopId == $foodStop->foodStopId) ? "selected" : "" ?>
                                    value="<?= $foodStop->foodStopId ?>"><?= $foodStop->name ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="row">
        <h1 class="display-4">
            <img class="img-fluid" style="height:80px" src="../images/icon.png"/> Dashboard


            <div class="float-end">
            <span class="display-6">
            <span class="subtitle"
                  style="color:#<?= $selectedFoodStop->hexColor ?>"><?= $selectedFoodStop->name ?></span>
            <span class="badge rounded-circle"
                  style="background-color:#<?= $selectedFoodStop->hexColor ?>;"><?= $selectedFoodStop->foodStopNumber ?></span>
                </span>
            </div>

        </h1>
        <div>

            <div class="float-end">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#placeOrderModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                         class="bi bi-plus-circle" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                    </svg>
                    Add Order
                </button>
                <button class="btn btn-primary"
                        onclick="changeFoodStop(<?= $selectedFoodStop->foodStopId ?>)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                         class="bi bi-arrow-clockwise" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                        <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                    </svg>
                    Reload
                </button>
            </div>
        </div>

    </div>


    <?php
    if ($errorOccurred) {
        ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h4 class="alert-heading"><?= $errorTitle ?></h4>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <p>
                <?= $errorMainDescription ?>
            </p>
            <hr>
            <p class="mb-0">
                <?= $errorSubDescription ?>
            </p>
        </div>
        <?php
    }
    ?>


    <hr/>
    <!-- Confirm modal -->
    <div class="modal" tabindex="-1" aria-hidden="true" id="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this listing and any reservation made on this listing?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="deleteListingButton" type="button" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pickup Modal -->
    <div class="modal fade" id="placeOrderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Select food listing being retrieved:</p>
                    <select class="form-select" id="selectedListing">
                    </select>
                </div>
                <div class="modal-body">
                    <p>Select quantity being retrieved:</p>
                    <select class="form-select" id="selectedQuantity">
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="submit">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Retrieval Amount</h5>
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
        var listings = [];
        var confirmModal = document.getElementById('confirmModal');
        var placeModal = document.getElementById('placeOrderModal');
        var deleteModal = document.getElementById('deleteModal');

        var modalReservationId;
        var listingIdToDelete;

        function getReservation(id) {
            var reservation = null;

            for (var i = 0; i < reservations.length; i++) {
                reservation = reservations[i];
                if (reservation.reservationId === parseInt(id)) {
                    break;
                }
            }
            return reservation;
        }

        function updatePickupListingQuantity(listing) {
            let select = placeModal.querySelector("#selectedQuantity");
            select.innerHTML = "";
            for (let i = 1; i <= listing.quantityRemaining; i++) {
                let option = document.createElement("option");
                option.value = i.toString();
                option.innerText = i.toString();
                select.appendChild(option);
            }
        }

        deleteModal.querySelector("#deleteListingButton").addEventListener('click', function event() {
            window.location = 'dashboard.php?foodstop=<?=$selectedFoodStop->foodStopId?>&action=delete&listingId=' + listingIdToDelete;
        });

        deleteModal.addEventListener('show.bs.modal', function (event) {
            let sourceButton = event.relatedTarget;
            listingIdToDelete = sourceButton.getAttribute("data-bs-id");
        });


        placeModal.querySelector("#selectedListing").addEventListener('change', function (event) {
            let select = placeModal.querySelector("#selectedListing");
            listings.forEach(listing => {
                if (parseInt(listing.listingId) === parseInt(select.value)) {
                    updatePickupListingQuantity(listing);
                }
            });

        });

        placeModal.querySelector("#submit").addEventListener("click", function (event) {
            let listingId = placeModal.querySelector("#selectedListing").value;
            let quantity = placeModal.querySelector("#selectedQuantity").value;
            window.location = 'dashboard.php?foodstop=<?=$selectedFoodStop->foodStopId?>&action=place&listingId=' + listingId + '&quantity=' + quantity;
        });

        placeModal.addEventListener('show.bs.modal', function (event) {
            // Show listings
            let select = placeModal.querySelector("#selectedListing");
            select.innerHTML = "";
            for (let i = 0; i < listings.length; i++) {
                let listing = listings[i];
                let option = document.createElement("option");
                option.value = listing.listingId;
                option.innerText = listing.title + " (" + listing.quantityRemaining + " Remaining)";
                select.appendChild(option);
            }

            updatePickupListingQuantity(listings[0]);

        });
        confirmModal.querySelector("#submit").addEventListener("click", function (event) {
            let reservation = getReservation(modalReservationId);
            let quantityRetrieved = confirmModal.querySelector("#selectedAmount").value;
            window.location = 'dashboard.php?foodstop=<?=$selectedFoodStop->foodStopId?>&action=retrieve&reservationId=' + reservation.reservationId + '&quantity=' + quantityRetrieved;
        });

        confirmModal.addEventListener('show.bs.modal', function (event) {
            let sourceButton = event.relatedTarget;

            modalReservationId = sourceButton.getAttribute("data-bs-id");
            let reservation = getReservation(modalReservationId);

            //TODO: Check if reservation is null
            let quantity = reservation.quantity;

            confirmModal.querySelector("#selectedAmount").innerHTML = "";
            for (var x = quantity; x > 0; x--) {
                var option = document.createElement("option");
                option.value = x;
                option.innerText = x;
                confirmModal.querySelector("#selectedAmount").appendChild(option);
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
            foreach ($reservations as $reservation) {
                $reservation = new Reservation($reservation);

                ?>
                <script>
                    reservation = <?=json_encode($reservation)?>;
                    reservations.push(reservation);
                </script>
            <?php

            $matchedListing = new Listing();
            foreach ($listings as $listing) {
                $listing = new Listing($listing);
                if ($listing->listingId == $reservation->listingId) {
                    $matchedListing = $listing;
                }
            }
            ?>
                <tr>
                    <th scope="row"><?= $matchedListing->title ?></th>
                    <td><?= date("g:ia", $reservation->timeExpired) ?></td>
                    <td><?= $reservation->quantity ?></td>
                    <td><?= $reservation->code ?></td>
                    <td>
                        <button class="btn btn-outline-success btn-sm" data-bs-toggle="modal"
                                data-bs-target="#confirmModal" data-bs-id="<?= $reservation->reservationId ?>">Retrieved
                        </button>
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

    <h3>Active Food Listings
        <div class="float-end">
            <button class="btn btn-success"
                    onclick="window.location.href='listing.php?action=create&foodstop=<?= $selectedFoodStopId ?>'">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                     class="bi bi-plus-circle" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                </svg>
                Add Listing
            </button>
    </h3>
    <table class="table table-sm table-hover">
        <thead>
        <tr>
            <th scope="col">Title</th>
            <th scope="col">Description</th>
            <th scope="col">Quantity Remaining</th>
            <th scope="col">Weight(oz)</th>
            <th scope="col">Date Added</th>
            <th scope="col">Date Expires</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($listings as $listing) {
            $listing = new Listing($listing);


            ?>
            <script>
                listing = <?=json_encode($listing)?>;
                listings.push(listing);
            </script>
            <tr>
                <th scope="row"><?= $listing->title ?></th>
                <td><?= $listing->description ?></td>
                <td><?= $listing->quantityRemaining ?>/<?= $listing->quantity ?></td>
                <td><?= $listing->weightOunces ?></td>
                <td><?= date("M jS g:ia", $listing->creationTime) ?></td>
                <td><?= date("M jS g:ia", $listing->expirationTime) ?></td>
                <td>
                    <button class="btn btn-outline-info btn-sm" onclick="window.location.href='listing.php?action=move&foodstop=<?= $selectedFoodStopId ?>&listingId=<?= $listing->listingId ?>'"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                            <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                        </svg> Move
                    </button>
                    <button class="btn btn-outline-secondary btn-sm"
                            onclick="window.location.href='listing.php?action=update&foodstop=<?= $selectedFoodStopId ?>&listingId=<?= $listing->listingId ?>'">
                        Edit
                    </button>
                    <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal"
                            data-bs-id="<?= $listing->listingId ?>">Delete
                    </button>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div style="height:50px"></div>
    <hr/>
    <div style="height:50px"></div>

    <h3>Food Listings (Expired Past 72 Hours)</h3>
    <p>
        This food is no longer available to serve on CampusPlate and should be physically removed from the food stop if
        any quantity is remaining.
    </p>
    <table class="table table-sm table-hover">
        <thead>
        <tr>
            <th scope="col">Title</th>
            <th scope="col">Description</th>
            <th scope="col">Quantity Remaining</th>
            <th scope="col">Date Expired</th>
            <th scope="col">Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($recentlyExpiredListings as $listing) {
            $listing = new Listing($listing);
            ?>
            <tr>
                <th scope="row"><?= $listing->title ?></th>
                <td><?= $listing->description ?></td>
                <td><?= $listing->quantityRemaining ?>/<?= $listing->quantity ?></td>
                <td><?= date("M jS g:ia", $listing->expirationTime) ?></td>
                <td>
                    <button class="btn btn-outline-secondary btn-sm"
                            onclick="window.location.href='listing.php?action=update&foodstop=<?= $selectedFoodStopId ?>&listingId=<?= $listing->listingId ?>'">
                        Edit
                    </button>
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
