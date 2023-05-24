<?php
require_once(__DIR__ . "/../../lib/Security.php");
require_once(__DIR__ . "/../../lib/Session.php");
require_once(__DIR__ . "/../../model/FoodStopsModel.php");
require_once(__DIR__ . "/../../model/types/FoodStop.php");
require_once(__DIR__ . "/../../model/types/User.php");
require_once(__DIR__ . "/../../model/types/Reservation.php");
require_once(__DIR__ . "/../../model/types/Listing.php");
require_once(__DIR__ . "/../../model/ReportingModel.php");
require_once(__DIR__ . "/../../model/AuthorizationModel.php");

if (!Session::isSessionValid() ) {
    header('Location: ' . "index.php");
}
if (!AuthorizationModel::isAdmin(Session::getSessionUserId()) ) {
    die("You are unauthorized to view this page");
}

// Check the various actions we should support
if (isset($_GET["action"])) {
    $action = $_GET["action"];
    $userId = $_GET["userId"];
    $foodStopId = $_GET["foodStopId"];

    if ($action == "removeRole") {
        FoodStopsModel::removeUserFromFoodStopManagerRole($userId, $foodStopId);
    }

    header('Location: admin.php');
}

$addRoleSuccess = true;
if (isset($_POST["action"])) {
    $action = $_POST["addRole"];
    $userEmail = $_POST["userEmail"];
    $foodStopId = $_POST["foodStopId"];

    $addRoleSuccess = FoodStopsModel::addUserToFoodStopManagerRole($userEmail, $foodStopId);
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

    <title>CampusPlate | Manage</title>
    <style>
        h3 {
            margin-left:15px;
        }
    </style>
</head>
<body>

<div class="container min-vh-100 h-100" id="login">
    <?php

    if (AuthorizationModel::isAdmin(Session::getSessionUserId()) ) {
    ?>
    <ul class="nav nav-tabs">
        <li class="nav-item">
            <a class="nav-link" href="dashboard.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link"  href="reporting.php">Reporting</a>
        </li>
        <li class="nav-item ">
            <a class="nav-link active" aria-current="page" href="admin.php">Admin</a>
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
            <img class="img-fluid" style="height:80px" src="../images/icon.png"/> Admin
        </h1>
    </div>

    <?php
        if (! $addRoleSuccess) {
            ?>
            <div class="alert alert-danger" role="alert">
                Unable to add user to food stop. Please make sure the user <?=$_POST["userEmail"]?> registered for an account first.
            </div>
    <?php
        }

    ?>
    <div class="modal" tabindex="-1" aria-hidden="true" id="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Role Removal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to remove <b class="user"></b> from managing the Food Stop <b class="foodstop"></b>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button id="removeRoleButton" type="button" class="btn btn-danger">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="js/admin.js"></script>

    <h2 class="mt-3">Admins and Food Stop Managers</h2>
    <div class="row">
        <div class="col-lg-12">
            <h5>Admins</h5>
            The following users are admins of the application and have access to each food stop.<br /><br />
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
            <h5>Current Food Stop Managers</h5>
            <table class="table table-sm table-hover table-responsive-lg">
                <thead>
                <tr>
                    <th scope="col">Food Stop Name</th>
                    <th scope="col">Food Stop Manager</th>
                    <th scope="col">Actions</th>
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
                            <td>
                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal"
                                        data-bs-foodstop="<?=htmlentities($manager['name'])?>"
                                        data-bs-foodstop-id="<?=$manager["foodStopId"]?>"
                                        data-bs-user="<?=$manager["userName"]?>"
                                        data-bs-user-id="<?=$manager["userId"]?>">Remove Role
                                </button>
                            </td>
                        </tr>
                        <?php
                    }
                ?>
                </tbody>
            </table>

            <div class="row m-5">
                <hr class=""/>
            </div>


            <h5>Add a Food Stop Manager</h5>
            <form action="admin.php" method="post">
                <input type="hidden" name="action" value="addRole" />
                <div class="row mb-3">
                    <label for="userEmail" class="col-sm-2 col-form-label">Enter User Email Address:</label>
                    <div class="col-sm-8">
                        <input type="email" class="form-control" id="userEmail" name="userEmail" aria-describedby="emailHelp">
                        <div id="emailHelp" class="form-text">Before adding a user, please make sure they registered for an account first.</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label for="foodStop" class="col-sm-2 col-form-label">Select Food Stop</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="foodStopId" name="foodStopId">
                            <?php
                                $foodStops = FoodStopsModel::getFoodStops();
                                foreach($foodStops as $foodStop) {
                                    ?>
                                    <option value='<?=$foodStop->foodStopId?>'><?=$foodStop->name?></option>";
                                    <?php
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                <div class="col-sm-2"></div>
                <div class="col-sm-8">
                    <button type="submit" class="btn btn-primary">Add User as Food Stop Manager</button>
                </div>
                </div>
            </form>
        </div>

    </div>
    <div class="mb-5"></div>
</div>
</body>
</html>
