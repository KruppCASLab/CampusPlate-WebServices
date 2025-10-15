<?php
require_once(__DIR__ . "/../lib/Config.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/main.css">
    <title>CampusPlate</title>
    <style>
        img {
            max-height:325px;
        }
        .qr-image {
            max-height:200px;
        }
    </style>
</head>
<body>
<div class="container">

    <h1 class="display-2"><img class="img-fluid" style="height:80px" src="images/icon.png"/> Welcome to Campus Plate</h1>
    <br />
    <h3>About Campus Plate</h3>
    <p class="lead">
        Campus Plate is a platform that allows users to recover food at various Food Stops around campus. This project is
        sponsored by the Environmental Protection Agency(EPA)'s P3 program with the primary goal of reducing food waste.
    </p>
    <hr />
    <div class="row mt-3">
        <div class="col-lg-6 col-sm-12">
            <h3>Install iOS App</h3>
            <p class="text">
                If you are looking to download the application and are a member of the <?=Config::getConfigValue("app", "organization")?> community, click on the following button from a mobile device:<div class="text-center"> <a href="https://apps.apple.com/us/app/campus-plate/id1560379783" class="text-center"><button class="btn btn-primary" href="">Install Campus Plate for iOS</button></a></div>
            </p>
            <p>
                Or, you can scan the following QR code from your iOS device:
            </p>
            <div class="text-center"><img class="img-fluid img-thumbnail qr-image" src="images/iosqrcode.png" /></div>

        </div>
        <div class="col-lg-6 col-sm-12">
            <h3>Install Android App</h3>
            <p class="text">
                If you are looking to download the application and are a member of the <?=Config::getConfigValue("app", "organization")?> community, click on the following button from a mobile device:<div class="text-center"> <a href="cp.apk" class="text-center"><button class="btn btn-success" href="">Install Campus Plate for Android</button></a><br /><small><i>Last Updated on <?=date("m/d/Y", filemtime("cp.apk"));?></i></small></div>
            </p>
            <p>
                Or, you can scan the following QR code from your Android device:
            </p>
            <div class="text-center"><img class="img-fluid img-thumbnail qr-image" src="images/androidqr.png" /></div>


        </div>
        </div>
    <div class="row mt-5">
        <div class="col-lg-6 col-sm-12 ">
            <h3>iOS App Tutorial</h3>
            <p>If you want to learn how to use the app, the video below provides a tutorial.</p>
            <div class="embed-responsive embed-responsive-16by9" >
            <iframe class="embed-responsive-item" style="max-width:100%" width="560" height="315" src="https://www.youtube.com/embed/kvyr83Ohrgo" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>

        <div class="col-lg-6 col-sm-12">
            <h3>Android App Tutorial</h3>
            <p>If you want to learn how to use the app, the video below provides a tutorial.</p>
            <div class="embed-responsive embed-responsive-16by9" >
                <iframe class="embed-responsive-item" style="max-width:100%" width="560" height="315" src="https://www.youtube.com/embed/1pjBDbHvOQA" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>


        </div>
    </div>

    <hr />

    <p class="mb-5">
        If you are a Food Stop manager, please click <a href="manage/">here</a> to access your Food Stop.<br /><br />
        This project was started by Dr. Franklin Lebo, Dr. Christy Walkuski, and Dr. Brian Krupp. If you are interested
        in discussing the project, please contact <a href="mailto:brian.krupp2@case.edu">Dr. Brian Krupp</a>.
    </p>
</div>

</body>
</html>