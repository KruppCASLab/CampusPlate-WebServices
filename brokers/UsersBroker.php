<?php

require_once(__DIR__ . "/../model/types/Listing.php");
require_once(__DIR__ . "/../model/ListingsModel.php");

class UsersBroker {
  static public function get($requestData) {
    $id = $requestData[0];
    if ($id == "") {

    } else {
    }
  }

  static public function post($requestData) {
    // Registration
  }

  //TODO: Complete for update
  static public function put($requestData) {
  }

  //TODO: Complete for delete
  static public function delete($requestData) {
  }
}

?>

