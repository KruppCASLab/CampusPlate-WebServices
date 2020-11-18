<?php
require_once (__DIR__ . "/Config.php");
require_once (__DIR__ . "/../model/UsersModel.php");
require_once (__DIR__ . "/../model/types/User.php");

class Security {
  static function isAuthenticationRequired($resource, $method) : bool {
    if ($resource == "users" && ($method == "post" || $method == "patch")) {
      return false;
    }
    else {
      return true;
    }
  }

  // Returns userId if valid, -1 if not
  static function authenticate($username, $password) : int {
      $result = UsersModel::authenticateUser($username, $password);
      $user = new User($result->data);
      if (isset($user->userId)) {
        return $user->userId;
      }
      else {
        return -1;
      }
  }
}
