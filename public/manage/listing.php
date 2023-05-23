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

$action = $_GET["action"];
$listingId = $_GET["listingId"];
$selectedFoodStopId = $_GET["foodstop"];

$error = null;

if (isset($_POST["action"])) {
    $creationDate = strtotime($_POST["creationDate"] . " " . $_POST["creationTime"]);
    $expirationDate = strtotime($_POST["expirationDate"] . " " . $_POST["expirationTime"]);

    // Need to set the fields that may have been lost
    $action = $_POST["action"];
    $selectedFoodStopId = $_POST["foodStopId"];
    $listingId = $_POST["listingId"];

    if ($_POST["action"] == "move") {
        // Get the listing where we are moving food
        $request = new Request(null, $listingId, null, Session::getSessionUserId());
        $response = ListingsController::get($request);
        $listing = new Listing($response->data);

        // Quantity to be moved
        $quantityToMove = $_POST["quantity"];
        $listing->quantity = $quantityToMove;
        $listing->foodStopId = $_POST["destinationFoodStop"];

        if ($quantityToMove > $listing->quantityRemaining) {
            $error = "Unable to move more quantity than what is available. Please check the quantity you are attempting to move.";
        }
        else {
            $request = new Request($listing, $listingId, "move", Session::getSessionUserId());
            $response = ListingsController::patch($request);
            header("Location: " . "dashboard.php?foodstop=" . $listing->foodStopId);
            die();
        }
    }
    else {
        $listing = new Listing();
        $listing->listingId = $_POST["listingId"];
        $listing->title = $_POST["title"];
        $listing->foodStopId = $_POST["foodStopId"];
        $listing->description = $_POST["description"];
        $listing->userId = Session::getSessionUserId();
        $listing->creationTime = $creationDate;
        $listing->expirationTime = $expirationDate;
        $listing->quantity = $_POST["quantity"];
        $listing->weightOunces = $_POST["weightOunces"];
        if (!(isset($listing->title) && is_numeric($listing->quantity) && is_numeric($listing->creationTime) && is_numeric($listing->expirationTime))) {
            $error = "Could not create or edit listing. Please check to make sure all fields are filled in correctly.";
        }
        else {
            if ($_POST["action"] == "create") {
                $request = new Request($listing, null, null, Session::getSessionUserId());
                $response = ListingsController::post($request);
            }
            else if ($_POST["action"] == "update") {
                $request = new Request($listing, $listing->listingId, null, Session::getSessionUserId());
                $response = ListingsController::put($request);
            }
            header("Location: " . "dashboard.php?foodstop=" . $listing->foodStopId);
            die();
        }
    }
}

$baseRequest = new Request(null, null, null, Session::getSessionUserId());

$foodStops = FoodStopsController::get($baseRequest)->data;
$selectedFoodStop = null;

// Default to first food stop
if (isset($selectedFoodStopId)) {
    foreach ($foodStops as $foodStop) {
        $foodStop = new FoodStop($foodStop);
        if ($foodStop->foodStopId == $selectedFoodStopId) {
            $selectedFoodStop = $foodStop;
        }
    }
}

$creationDate = time();
$expirationDate = time() + 60 * 60 * 24 * 2;// Two days ahead



if ($action == "update" || $action == "move") {
    $request = new Request(null, $listingId, null, Session::getSessionUserId());
    $response = ListingsController::get($request);

    $listing = new Listing($response->data);

    $creationDate = $listing->creationTime;
    $expirationDate = $listing->expirationTime;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="js/bootstrap.js"></script>
    <script src="js/main.js"></script>
    <script src="js/jquery.js"></script>
    <script src="js/jquery-ui.js"></script>

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/jquery-ui.css">
    <link rel="stylesheet" href="css/listing.css">
    <title>CampusPlate | Manage</title>
    <script src="js/listing_setup.js"></script>

</head>
<body>

<div class="container min-vh-100 h-100" id="login">
    <div class="row">
        <h1 class="display-4">
            <img class="img-fluid" style="height:80px"
                 src="../images/icon.png"/> <?= ucfirst($action) ?> Listing
            <div class="float-end">
            <span class="display-6">
            <span class="subtitle"
                  style="color:#<?= $selectedFoodStop->hexColor ?>"><?= $selectedFoodStop->name ?></span>
            <span class="badge"
                  style="background-color:#<?= $selectedFoodStop->hexColor ?>; border-radius:50%"><?= $selectedFoodStop->foodStopNumber ?></span>
                </span>
            </div>
        </h1>
    </div>

    <?php
    if (isset($error)) {
        ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <h4 class="alert-heading">Error Occurred</h4>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <p>
                <?= $error ?>
            </p>
        </div>
        <?php
    }
    ?>
    <div class="row mt-3">
        <form action="listing.php" method="post">
            <input type="hidden" name="foodStopId" value="<?= $selectedFoodStopId ?>"/>
            <input type="hidden" name="listingId" value="<?=$listingId ?>"/>
            <input type="hidden" name="action" value="<?= $action ?>"/>
            <div class="col-lg-6">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= $listing->title ?>" <?=($action == "move")? "disabled" : ""?>>
                </div>
            </div>
            <div class="row">
                <div class="col-4">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity<?=($action == "move") ? " to Move to Food Stop ( " . $listing->quantityRemaining. " Available to Move)" : ""?></label>
                        <input type="text" class="form-control" id="quantity" name="quantity"
                               value="<?= ($action == "move") ? 1 : $listing->quantity ?>">
                    </div>
                </div>
            </div>
            <?php
            if ($action == "move") {
            ?>
                <div class="row">
                    <div class="col-8">
                        <label for="destinationFoodStop" class="form-label">Select Destination Food Stop</label>
                        <select class="form-select" id="destinationFoodStop" name="destinationFoodStop">
                            <?php
                            foreach($foodStops as $foodStop) {
                                $foodStop = new FoodStop($foodStop);

                                // Skip because the destination should be different
                                if ($selectedFoodStopId == $foodStop->foodStopId) {
                                    continue;
                                }
                                ?>
                                <option value="<?=$foodStop->foodStopId?>"><?=$foodStop->name?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <?php
            }
            else {
            ?>
            <div class="row">
                <div class="col-2">
                    <div class="mb-3">
                        <label for="weight" class="form-label">Weight (Ounces Per Item)</label>
                        <input type="text" class="form-control" id="weightOunces" name="weightOunces"
                               value="<?= $listing->weightOunces ?>">
                    </div>
                </div>
            </div>


            <div class="col-lg-12">
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea type="text" class="form-control" id="description"
                              name="description"><?= $listing->description ?></textarea>
                </div>
            </div>


            <div class="row">
                <div class="col-4">
                    <div class="mb-3">
                        <label for="creationDate" class="form-label">Creation Date and Time</label> <br/>

                        <input type="text" class="form-control dateControl" id="creationDate" name="creationDate"
                               value="<?= date("m/d/Y", $creationDate) ?>"
                               placeholder="<?= date("m/d/Y", $creationDate) ?>"/>
                        <input type="text" class="form-control dateControl" id="creationTime" name="creationTime"
                               style="max-width:150px;display: inline-block" value="<?= date("H:i", $creationDate) ?>"/>

                    </div>
                </div>

                <div class="col-4">
                    <div class="mb-3">
                        <label for="expirationDate" class="form-label">Expiration Date and Time</label> <br/>
                        <input type="text" class="form-control dateControl" id="expirationDate" name="expirationDate"
                               value="<?= date("m/d/Y", $expirationDate) ?>"
                               placeholder="<?= date("m/d/Y", $expirationDate) ?>"/>
                        <input type="text" class="form-control dateControl" id="expirationTime" name="expirationTime"
                               style="max-width:150px;display: inline-block"
                               value="<?= date("H:i", $expirationDate) ?>"/>
                    </div>
                </div>
                <div class="row">
                    <div class="form-text mt-3">Time can be entered in 24 hour format, for example: 8:24pm would be 20:24, or you can use am and pm.</div>
                </div>
                <?php
                }
                ?>
                <div class="col-lg-12 mt-4">
                    <a type="reset" class="btn btn-danger" role="button" href="dashboard.php">Cancel</a>
                    <button type="submit" class="btn btn-primary ms-3">Submit</button>
                </div>
        </form>
    </div>
</div>
</body>
</html>
