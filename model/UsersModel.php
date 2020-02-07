<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/DBResponse.php");

class UsersModel {

  static public function createUser(User $user): DBResponse {
    $db = new Database();

    $sql = "INSERT INTO tblUsers(username, password, emailAddress, pin) VALUES (?, ?, ?,?)";

    $db->executeSql($sql, "sssi", array($user->username, Security::hashPassword($user->password), $user->emailAddress, $user->pin));

    return new DBResponse(null, $db->lastError);
  }

}