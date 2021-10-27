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
    <script src='https://cdn.plot.ly/plotly-2.4.2.min.js'></script>
    <title>CampusPlate | Manage</title>
</head>
<body>

<div class="container min-vh-100 h-100" id="login">
    <?php

    if (AuthorizationModel::isAdmin(Session::getSessionUserId())) {
    ?>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="reporting.php">Reporting</a>
        </li>
        <li class="nav-item ">
            <a class="nav-link disabled" href="#">Users</a>
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
    ?>

    <h2 class="mt-3">Overall Food Recovery</h2>
    <div class="row mt-3">
        <div class="col-md-6 col-sm-12 text-center ">
            <h1 class="display-1 mainColor"><?=$total?></h1>
            <p>Items Recovered Since Inception</p>
        </div>
        <div class="col-md-6 col-sm-12 text-center">
            <h1 class="display-1 mainColor"><?=$lastWeek?></h1>
            <p>Items Recovered Last Week</p>
        </div>
    </div>
    <div id="byDate">
    </div>
    <script>
        <?php
            $results = ReportingModel::getItemsPerDay();
            ?>
        let data = [
            {
                x: [
                    <?php
                    for ($i = 0; $i < sizeof($results); $i++) {
                        echo "'" . $results[$i]["createdDate"] . "'";
                        if ($i < sizeof($results) - 1) echo ",";
                    }

                    ?>],
                y: [
                    <?php

                    for ($i = 0; $i < sizeof($results); $i++) {
                        echo $results[$i]["numPerDate"];
                        if ($i < sizeof($results) - 1) echo ",";
                    }
                    ?>],
                type: 'bar',
                marker: {
                    color: '#EBB500'

                }
            }
        ];
        Plotly.newPlot("byDate", data);
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






    <div style="height:50px"></div>
    <hr/>
    <div style="height:50px"></div>


</div>
</body>
</html>
