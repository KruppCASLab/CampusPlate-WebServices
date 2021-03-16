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

    $listing = new Listing();
    $listing->listingId = $_POST["listingId"];
    $listing->title = $_POST["title"];
    $listing->foodStopId = $_POST["foodStopId"];
    $listing->description = $_POST["description"];
    $listing->userId = Session::getSessionUserId();
    $listing->creationTime = $creationDate;
    $listing->expirationTime = $expirationDate;
    $listing->quantity = $_POST["quantity"];

    if (! (isset($listing->title) && is_numeric($listing->quantity) && is_numeric($listing->creationTime) && is_numeric($listing->expirationTime))) {
        $error = "Could not create or edit listing. Please check to make sure all fields.";
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


$listing = null;
if ($action == "update") {
    $listingId = $_GET["listingId"];
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-b5kHyXgcpbZJO/tY9Ul7kGkf1S0CWuKcCD38l8YkeH8z8QjE0GmW1gYU5S9FOnJ0"
            crossorigin="anonymous"></script>

    <script src="js/main.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <title>CampusPlate | Manage</title>
    <script>
        $(function () {
            $("#creationDate").datepicker();
            $("#expirationDate").datepicker();
        });

    </script>
    <style>
        .dateControl {
            max-width: 150px;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="container min-vh-100 h-100" id="login">
    <div class="row">
        <h1 class="display-4">
            <img class="img-fluid" style="height:80px"
                 src="images/icon.png"/> <?= ucfirst($action)?> Listing
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
            <input type="hidden" name="foodStopId" value="<?=$selectedFoodStopId?>" />
            <input type="hidden" name="listingId" value="<?=$listingId?>" />
            <input type="hidden" name="action" value="<?=$action?>" />
            <div class="col-lg-6">
                <div class="mb-3">
                    <label for="title" class="form-label">Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?=$listing->title?>">
                </div>
            </div>
            <div class="col-2">
                <div class="mb-3">
                    <label for="quantity" class="form-label">Quantity</label>
                    <input type="text" class="form-control" id="quantity" name="quantity" value="<?=$listing->quantity?>">
                </div>
            </div>
            <div class="col-lg-12">
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea type="text" class="form-control" id="description" name="description"><?=$listing->description?></textarea>
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
                           style="max-width:150px;display: inline-block" value="<?=date("H:i", $creationDate) ?>"/>
                    <div class="form-text mt-3">Time is in 24 hour format, for example: 8:24pm would be 20:24</div>
                </div>
            </div>

            <div class="col-4">
                <div class="mb-3">
                    <label for="expirationDate" class="form-label">Expiration Date and Time</label> <br/>
                    <input type="text" class="form-control dateControl" id="expirationDate" name="expirationDate"
                           value="<?= date("m/d/Y", $expirationDate) ?>"
                           placeholder="<?= date("m/d/Y", $expirationDate) ?>"/>
                    <input type="text" class="form-control dateControl" id="expirationTime" name="expirationTime"
                           style="max-width:150px;display: inline-block" value="<?=date("H:i", $expirationDate) ?>"/>
                </div>
            </div>
            <div class="col-lg-12 mt-4">
                <button type="reset" class="btn btn-danger" onclick="window.location.href='dashboard.php'">Cancel</button>
                <button type="submit" class="btn btn-primary ms-3">Submit</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
