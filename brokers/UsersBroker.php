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
    $userName = $requestData[0];

    $user = new User($requestData[1]);
    $user->userName = $userName;

    $response = new Response();

    if ($user->pin != null){
      if(UsersModel::checkPinAndUser($user)->status === 0){
        UsersModel::updateVerifiedFlag($user);

        // TODO: Create GUID and store that in DB
        $response->data = self::generateGUID();

        // TODO: Return response containing GUID to use for auth
        // TODO: On iOS, store the GUID and the username in the keychain

        $response->status = 0;

        return $response;
      }
      else{
        return new Response(null, 2); // Use 2 to indicate invalid match
      }
    }
    else{
      return new Response(null, 1); // Use 1 to indicate they did not send pin
    }
  }

  //TODO: Complete for delete
  static public function delete($requestData) {
  }

  static private function randomPin(){
    $randomPin = mt_rand(100000, 999999);
    return $randomPin;
  }

  static private function generateGUID(){
    $guid = bin2hex(openssl_random_pseudo_bytes(16));
    return $guid;
  }

}

?>

