<?php
require_once(__DIR__ . "/../brokers/UsersBroker.php");
require_once(__DIR__ . "/../brokers/ListingsBroker.php");
require_once(__DIR__ . "/../lib/Security.php");

header('Content-Type: application/json');


$request = explode("/", $_SERVER["PATH_INFO"]);
$method = strtolower($_SERVER["REQUEST_METHOD"]);

$resource = $request[1];
$resource = ucfirst($resource) . "Broker";

$userId = -1;

// User is attempting to register
//TODO: Uncomment for authentication

//if (!($resource == "SecurityBroker" && $method == "post")) {
//  $username = $_SERVER['PHP_AUTH_USER'];
//  $password = $_SERVER['PHP_AUTH_PW'];
//  // TODO: Auth the user
//  // TODO: if auth fails, die("Error")
//  $userId = authenticate($username, $password);
//
//  if ($userId === false) {
//    http_response_code(401);
//    die("Error");
//  }
//}


$requestBody = json_decode(file_get_contents("php://input"));


$requestData = array();

if (method_exists($resource, $method)) {
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

  $response = call_user_func(array($resource, $method), $requestData);

  echo json_encode($response);
} else {
  http_response_code(405);
}


?>
