<?php
require_once(__DIR__ . "/../brokers/UsersBroker.php");
require_once(__DIR__ . "/../brokers/ListingsBroker.php");
require_once(__DIR__ . "/../lib/Security.php");

header('Content-Type: application/json');

// Break apart path to determine broker and method
$request = explode("/", $_SERVER["PATH_INFO"]);
$method = strtolower($_SERVER["REQUEST_METHOD"]);

$resource = $request[1];
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

$requestBody = json_decode(file_get_contents("php://input"));
$requestData = array();

// Check if broker supports method
if (method_exists($broker, $method)) {
  http_response_code(200);

  // Check for an ID
  if ($method == "get" || $method == "put" || $method == "delete" || $method == "patch") {
    $id = $request[2];
    array_push($requestData, $id);
  }
  // Check if we are sending JSON
  if ($method == "post" || $method == "put" || $method == "patch") {
    $requestBody->userId = $userId;
    array_push($requestData, $requestBody);
  }

  $response = call_user_func(array($broker, $method), $requestData);

  echo json_encode($response);
} else {
  http_response_code(405);
}