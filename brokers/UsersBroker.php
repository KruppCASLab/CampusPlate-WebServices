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
  static public function patch($requestData) {

    //TODO: Create user object
    $user = new User($requestData[0]);

    //TODO: Check to see if pin is not null, if null return error

    if ($user->pin != null){
      // TODO: Here you need to check your response from this, and then create the appropriate response object and return
      UsersModel::checkPinAndUser($user);
      
      //TODO: If your response is good, then set the verified flag
      UsersModel::updateVerifiedFlag($user);
    }else{

      //DBResponse->$status = 1
    }


  }

  //TODO: Complete for delete
  static public function delete($requestData) {
  }

  static private function randomPin(){
    $randomPin = mt_rand(100000, 999999);
    return $randomPin;
  }

}

?>

