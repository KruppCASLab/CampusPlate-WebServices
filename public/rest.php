<?php

// Available Services
require_once(__DIR__ . "/../controllers/ListingsController.php");
require_once(__DIR__ . "/../controllers/FoodStopsController.php");
require_once(__DIR__ . "/../controllers/ReservationsController.php");
require_once(__DIR__ . "/../controllers/UsersController.php");
//require_once(__DIR__ . "/../controllers/HoursController.php");
require_once(__DIR__ . "/../model/types/Request.php");
require_once(__DIR__ . "/../lib/Security.php");


// Break apart path to determine controller and method
$path = explode("/", $_SERVER["PATH_INFO"]);
$method = strtolower($_SERVER["REQUEST_METHOD"]);

$resource = strtolower($path[1]);
$controller = ucfirst($resource) . "Controller";

$userId = -1;

// Check if service requires authentication
if (Security::isAuthenticationRequired($resource, $method)) {
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];

    $userId = Security::authenticateUser($username, $password);

    if ($userId == -1) {
        http_response_code(401);
        die();
    }
}

$data = json_decode(file_get_contents("php://input"));
$request = new Request();
$request->userId = $userId;

// Check if controller supports method
if (method_exists($controller, $method)) {
    http_response_code(200);

    // If we have a get, put, delete, or patch, we may have an id and path on the request
    if ($method == "get" || $method == "put" || $method == "delete" || $method == "patch") {
        // Check if we passed an ID or some param
        if (is_numeric($path[2])) {
            $request->id = $path[2];
            $request->param = $path[3];
        }
        else {
            // Otherwise second field could be ID or param
            $request->id = $path[2];
            $request->param = $path[2];
        }
    }
    // Check if we are sending JSON
    if ($method == "post" || $method == "put" || $method == "patch") {
        $request->data = $data;
    }

    $response = call_user_func(array($controller, $method), $request);

    header('Content-Type: application/json');
    echo json_encode($response);

}
else {
    http_response_code(405);
}