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
    <h1>Campus Plate | Login</h1>
    <form action="<?=$_SERVER['PHP_SELF']?>" method="post">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" aria-describedby="loginHelp">
            <div id="loginHelp" class="form-text">This is your BW username</div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password">
        </div>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
        <div class="alert alert-danger" role="alert" id="invalidAlert" style="display:none">
            Invalid login, please try again.
        </div>
    </form>
</div>
</body>
</html>
