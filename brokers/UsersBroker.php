<?php

require_once(__DIR__ . "/../model/types/User.php");
require_once(__DIR__ . "/../model/UsersModel.php");

class UsersBroker {
  static public function get($requestData) {
    $id = $requestData[0];
    if ($id == "") {

    } else {
    }
  }

  // Registration
  static public function post($requestData) {
    $user = new User($requestData[0]);

    return UsersModel::createUser($user);
  }

  //TODO: Complete for update
  static public function put($requestData) {
  }

  //TODO: Complete for delete
  static public function delete($requestData) {
  }
}

?>

