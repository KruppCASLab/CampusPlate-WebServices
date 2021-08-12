<?php
require_once(__DIR__ . "/../lib/Security.php");
require_once(__DIR__ . "/../lib/Session.php");
require_once(__DIR__ . "/../controllers/UsersController.php");
require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/Request.php");

// Step 1 - Username and pin
$username = $_POST["username"];
$pin = $_POST["pin"];

// Step 2 - Passwords
$password = $_POST["password"];
$passwordVerify = $_POST["passwordVerify"];

$pinVerified = null;

$passwordMismatch = null;
$passwordStrengthValid = null;

$showPasswordForm = false;

$passwordResetComplete = false;

$errors = array();

if (isset($password) && isset($passwordVerify)) {
    $pinVerified = Security::verifyUserPin($username, $pin);
    if ($pinVerified === false) {
        sleep(5);
        $showPasswordForm = true;
    }
    else {
        $passwordStrengthValid = true;
        if ($password != $passwordVerify) {
            array_push($errors, "Passwords do not match");
        }
        if (strlen($password) < 10) {
            array_push($errors, "Password too short");
        }
        if (!preg_match("#[0-9]+#", $password)) {
            array_push($errors, "Password must include at least one number");
        }
        if (!preg_match("#[a-z]+#", $password)) {
            array_push($errors, "Password must include at least one lower case letter");
        }
        if (!preg_match("#[A-Z]+#", $password)) {
            array_push($errors, "Password must include at least one upper case letter");
        }

        if (sizeof($errors) > 0) {
            $passwordStrengthValid = false;
            $showPasswordForm = true;
        }
        else {
            // Passwords are good, verify pin again and set password
            $pinVerified = Security::verifyUserPin($username, $pin);
            if ($pinVerified) {
                Security::resetPassword($username, $password);
                $passwordResetComplete = true;
                $showPasswordForm = false;
            }
        }
    }
}
else if (isset($username)) {
    $user = new User(null);
    $user->userName = $username;

    $credential = new Credential(null);
    $credential->type = 1; // Web
    $credential->label = "Web";

    $user->credential = $credential;
    $request = new Request($user);
    $response = UsersController::post($request);

    $showPasswordForm = true;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <title>CampusPlate | Reset Password</title>
</head>
<body>
<div class="container min-vh-100 h-100" id="login">
    <div class="row">
        <div class="col-lg-12">
            <h1><img class="img-fluid" style="height:80px" src="images/icon.png"/>Campus Plate | Reset Password</h1>
        </div>
    </div>
    <div class="row mt-4">
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
            <?php
            // Show form for entering pin
            // Show form for entering password
            if ($showPasswordForm === true) {
                ?>
                <input type="hidden" name="username" value="<?= $username ?>"/>
                <input type="hidden" name="action" value="resetpassword"/>
                <div class="col-lg-12">
                    <p>
                        Enter the pin that you received in your email. Then, enter your new password.
                        Your password should be at least 10 characters, use at least one
                        number, one special character, and an upper case letter.
                    </p>
                </div>
                <div class="mb-3 col-lg-4">
                    <label for="pin" class="form-label">Pin</label>
                    <input type="text" class="form-control" name="pin" id="pin">
                </div>
                <?php

                ?>
                <div class="alert alert-danger" role="alert" id="invalidAlert"
                     style="display:<?= ($pinVerified === false) ? "block" : "none" ?>">
                    Unable to verify pin. Please verify the username and pin that you are entering.
                </div>

                <div class="mb-3 col-lg-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password">
                </div>
                <div class="mb-3 col-lg-4">
                    <label for="passwordVerify" class="form-label">Verify Password</label>
                    <input type="password" class="form-control" name="passwordVerify" id="passwordVerify">
                </div>

                <div class="alert alert-danger" role="alert" id="invalidAlert"
                     style="display:<?= ($passwordStrengthValid === false) ? "block" : "none" ?>">
                    Password requirement are not met:<br/>
                    <ul>
                        <?php
                        foreach ($errors as $error) {
                            ?>
                            <li><?= $error ?></li>
                            <?php
                        }
                        ?>
                    </ul>
                </div>
                <?php
            }
            else if (!isset($pinVerified) || $pinVerified === false) {
                ?>
                <div class="col-lg-12">
                    <p>
                        Enter your username and a pin will be sent to your your email address to reset your password.
                    </p>
                </div>

                <div class="mb-3 col-lg-4">
                    <label for="username" class="form-label">Username</label>
                    <input type="email" class="form-control" name="username" aria-describedby="loginHelp" id="username">
                    <div id="loginHelp" class="form-text">This is your BW email address</div>
                </div>
                <?php
            }
            else if ($passwordResetComplete == true) {
                ?>
                <h3 class="mb-3">Your password has been reset!</h3>
                <p>You can now login to the portal using your new password. <a href="manage/index.php">Access Portal</a>
                </p>

                <?php
            }
            ?>
            <?php
            if (!$passwordResetComplete) {
                ?>
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Continue</button>
                </div>
                <?php
            }
            ?>
        </form>
    </div>
</div>
</body>
</html>
