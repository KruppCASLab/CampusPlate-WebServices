<?php
require_once(__DIR__ . "/../../lib/Security.php");
require_once(__DIR__ . "/../../lib/Session.php");
require_once(__DIR__ . "/../../model/types/FoodStop.php");
require_once(__DIR__ . "/../../model/types/User.php");
require_once(__DIR__ . "/../../model/types/Reservation.php");
require_once(__DIR__ . "/../../model/types/Listing.php");
require_once(__DIR__ . "/../../model/ReportingModel.php");
require_once(__DIR__ . "/../../model/AuthorizationModel.php");

if (!Session::isSessionValid() ) {
    header('Location: ' . "index.php");
}

$action = $_GET["action"];
$filter = $_GET["filter"];
if ($action === "getPlotData") {

    $x = array();
    $y = array();
    if ($filter == "items") {
        $results = ReportingModel::getItemsPerDay();
        foreach ($results as $result) {
            array_push($x, $result["createdDate"]);
            array_push($y, $result["numPerDate"]);
        }
    }
    else if ($filter == "weight") {
        $results = ReportingModel::getWeightRecoveredByDayInPounds();
        foreach ($results as $result) {
            array_push($x, $result["createdDate"]);
            array_push($y, $result["total"]);
        }
    }
    $data["x"] = $x;
    $data["y"] = $y;
    die(json_encode($data));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="js/bootstrap.js"></script>
    <script src="js/main.js"></script>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="css/reporting.css">
    <script src='js/plotly-2.18.2.min.js'></script>
    <title>CampusPlate | Manage</title>
</head>
<body>

<div class="container min-vh-100 h-100" id="login">
    <?php

    if (AuthorizationModel::isAdmin(Session::getSessionUserId()) || AuthorizationModel::isAFoodStopManager(Session::getSessionUserId())) {
    ?>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="reporting.php">Reporting</a>
        </li>
        <li class="nav-item ">
            <a class="nav-link" href="admin.php">Admin</a>
        </li>
    </ul>
    <?php
    }
    else {
        die("You are not authorized to view this page.");
    }
    ?>

    <div class="row mt-3">
        <h1 class="display-4">
            <img class="img-fluid" style="height:80px" src="../images/icon.png"/> Reporting
        </h1>
    </div>
    <?php
        $total = ReportingModel::getTotalItemsRecovered();
        $lastWeek = ReportingModel::getItemsRecoveredLastWeek();
        $lastWeekNotRecovered = ReportingModel::getItemsNotRecoveredLastWeek();
        $totalNotRecovered = ReportingModel::getTotalItemsNotRecovered();
        $weight = ReportingModel::getTotalWeightRecoveredInPounds();
        $weightNotRecovered = ReportingModel::getTotalWeightNotRecoveredInPounds();
    ?>

    <h2 class="mt-3">Overall Food Recovery</h2>
    <h3>Items Recovered</h3>
    <div class="row mt-3">
        <div class="col-md-4 col-sm-12 text-center ">
            <h1 class="display-1 mainColor"><?=$total?></h1>
            <p>Items Recovered Since Inception</p>
        </div>
        <div class="col-md-4 col-sm-12 text-center">
            <h1 class="display-1 mainColor"><?=$lastWeek?></h1>
            <p>Items Recovered Last Week</p>
        </div>
        <div class="col-md-4 col-sm-12 text-center">
            <h1 class="display-1 mainColor"><?=round($weight, 0)?></h1>
            <p>Total Weight (lbs) Recovered <br />(<small>For Listings Reporting Weight</small>)</p>
        </div>
    </div>
    <h3>Items Not Recovered</h3>
    <div class="row">
        <div class="col-md-4 col-sm-12 text-center ">
            <h1 class="display-1 secondaryColor"><?=$totalNotRecovered?></h1>
            <p>Total Items Not Recovered Since Inception</p>
        </div>
        <div class="col-md-4 col-sm-12 text-center">
            <h1 class="display-1 secondaryColor"><?=$lastWeekNotRecovered?></h1>
            <p>Items Not Recovered Last Week</p>
        </div>
        <div class="col-md-4 col-sm-12 text-center ">
            <h1 class="display-1 secondaryColor"><?=round($weightNotRecovered, 0)?></h1>
            <p>Total Weight Composted in Grind2Energy<br />(<small>For Listings Reporting Weight</small>)</p>
        </div>
    </div>
    <h3>Items Recovered By Day</h3>
    <div id="byDate">
    </div>
    <h3>Weight By Day (Pounds)</h3>
    <div id="weightByDate">
    </div>
    <script src="js/reporting.js">
    </script>

    <h2 class="mt-3">User Statistics</h2>
    <?php
    $usersPastWeek = ReportingModel::getNumberOfUsersUsingAppInPastWeek();
    $newAccounts = ReportingModel::getNumberOfCredentialsCreatedInAppInPastWeek();
    ?>
    <div class="row mt-3">
        <div class="col-md-6 col-sm-12 text-center ">
            <h1 class="display-1 mainColor"><?=$usersPastWeek?></h1>
            <p>Number of Users in Past Week</p>
        </div>
        <div class="col-md-6 col-sm-12 text-center">
            <h1 class="display-1 mainColor"><?=$newAccounts?></h1>
            <p>New Credentials for Users Created in Past Week</p>
        </div>
    </div>

    <h2 class="mt-3">Admins and Food Stop Managers</h2>
    <div class="row">
        <div class="col-lg-12">
            <h5>Admins</h5>
            The following users are admins of the application and have access to each food stop:<br /><br />
            <ul>
            <?php
              $admins = ReportingModel::getAdminUsers();
              foreach($admins as $admin) {
                  ?>
                  <li><?=$admin["userName"]?></li>
                  <?php
              }
            ?>
            </ul>
            <br />
            <h5>Food Stop Managers</h5>
            <table class="table table-sm table-hover table-responsive-lg">
                <thead>
                <tr>
                    <th scope="col">Food Stop Name</th>
                    <th scope="col">Food Stop Manager</th>
                </tr>
                </thead>
                <tbody>
                <?php
                    $managers = ReportingModel::getFoodStopManagers();
                    foreach($managers as $manager) {
                        ?>
                        <tr>
                            <td><?=$manager['name']?></td>
                            <td><?=$manager['userName']?></td>
                        </tr>
                        <?php
                    }
                ?>
                </tbody>
            </table>
        </div>

    </div>
    <hr/>
    <div class="mt-5"></div>
</div>
</body>
</html>
