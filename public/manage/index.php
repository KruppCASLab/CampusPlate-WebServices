<?php
require_once(__DIR__ . "/../../lib/Security.php");
require_once(__DIR__ . "/../../lib/Session.php");

$username = $_POST["username"];
$password = $_POST["password"];

$loginAttempt = false;
if (isset($username) && isset($password)) {
    $loginAttempt = true;
    $userId = Security::authenticateUser($username, $password);

    if ($userId != -1) {
        Session::setSessionUserId($userId);
        // Set session
      header('Location: '. "dashboard.php");
      die();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="js/main.js"></script>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <title>CampusPlate | Manage</title>
</head>
<body>
<div class="container min-vh-100 h-100" id="login">
    <div class="mx-auto" style="width: 500px;">
        <h1>Campus Plate | Manage</h1>
        <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" aria-describedby="loginHelp">
                <div id="loginHelp" class="form-text">This is your BW email address</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password">
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
            <div class="alert alert-danger" role="alert" id="invalidAlert" style="display:<?=($loginAttempt) ? "block" : "none"?>">
                Invalid login, please try again.
            </div>
            <div ></div>
            <hr class="m-lg-5"/>
            If you do not remember your account, please contact the administrator.
        </form>
    </div>

</div>
</body>
</html>
