<?php

require_once(__DIR__ . "/../brokers/ListingsBroker.php");
require_once(__DIR__ . "/../brokers/FoodStopsBroker.php");
require_once(__DIR__ . "/../lib/Security.php");
require_once(__DIR__ . "/../brokers/UsersBroker.php");
require_once(__DIR__ . "/../model/types/Request.php");

// Break apart path to determine broker and method
$path = explode("/", $_SERVER["PATH_INFO"]);
$method = strtolower($_SERVER["REQUEST_METHOD"]);

$resource = $path[1];
$broker = ucfirst($resource) . "Broker";

$userId = -1;

// Check if service requires authentication
if (Security::isAuthenticationRequired($resource, $method)) {
  $username = $_SERVER['PHP_AUTH_USER'];
  $password = $_SERVER['PHP_AUTH_PW'];

  $userId = Security::authenticate($username, $password);

  if ($userId == -1) {
    http_response_code(401);
    die();
  }
}

$data = json_decode(file_get_contents("php://input"));
$request = new Request();

// Check if broker supports method
if (method_exists($broker, $method)) {
  http_response_code(200);

  // If we have a get, put, delete, or patch, we may have an id and path on the request
  if ($method == "get" || $method == "put" || $method == "delete" || $method == "patch") {
    $request->id = $path[2];
    $request->param = $path[3];
  }
  // Check if we are sending JSON
  if ($method == "post" || $method == "put" || $method == "patch") {
    $data->userId = $userId;
    $request->data = $data;
  }

  $response = call_user_func(array($broker, $method), $request);

  // Check if image is requested then return appropriate content type and data
  if ($method == "get" && $request->param == "image") {
    header('Content-Type: image/jpeg');
    echo $response->data;
  }
  else {
    header('Content-Type: application/json');
    echo json_encode($response);
  }
}
else {
  http_response_code(405);
}