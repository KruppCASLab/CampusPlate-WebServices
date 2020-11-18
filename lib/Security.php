<?php
require_once (__DIR__ . "/Config.php");
require_once (__DIR__ . "/../model/UsersModel.php");
require_once (__DIR__ . "/../model/types/User.php");

class Security {

  /**
   * Determines if the user need to authenticate when accessing a specific service
   * @param $resource
   * @param $method
   * @return bool
   */
  static function isAuthenticationRequired($resource, $method) : bool {
    if ($resource == "users" && ($method == "post" || $method == "patch")) {
      return false;
    }
    else {
      return true;
    }
  }

  /**
   * Returns the userId if a valid userId exists, otherwise, returns -1
   * @param $username
   * @param $password
   * @return int
   */
  static function authenticate($username, $password) : int {
      return UsersModel::authenticateUser($username, $password);

  }

  /**
   * Generates random pin from 100000 to 999999
   * @return int
   */
  static public function randomPin(){
    return mt_rand(100000, 999999);

  }

  /**
   * Generates GUID
   * @return string
   */
  static public function generateGUID(){
    return bin2hex(openssl_random_pseudo_bytes(16));
  }
}
