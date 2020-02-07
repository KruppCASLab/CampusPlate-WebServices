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
    $user->pin = self::randomPin();
    return UsersModel::createUser($user);
  }

  //TODO: Complete for update
  static public function put($requestData) {

  }

  //TODO: Complete for delete
  static public function delete($requestData) {
  }

  static private function randomPin(){
//    $randomPin = mt_rand(100000, 999999);
    $randomPin = 123456;
    return $randomPin;
  }

}

?>

