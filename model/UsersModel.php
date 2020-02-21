<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/DBResponse.php");

class UsersModel {

  static public function createUser(User $user): DBResponse {
    $db = new Database();

    $sql = "INSERT INTO tblUsers(userName, password, emailAddress, pin) VALUES (?, ?, ?,?)";

    $db->executeSql($sql, "sssi", array($user->userName, Security::hashPassword($user->password), $user->emailAddress, $user->pin));

    return new DBResponse(null, $db->lastError);
  }

  //TODO: Create method to check if pin and user combination exist

    static public function checkPinAndUser(User $user): DBResponse {
        $db = new Database();

        $sql = "SELECT pin FROM tblUsers WHERE userName = ?";

        $db->executeSql($sql, "s", array($user->userName));

        return new DBResponse(null, $db->lastError);
    }

    //TODO: Update account to be verified

    static public function updateVerifiedFlag(User $user): DBResponse {
        $db = new Database();

        $sql = "UPDATE tblUsers SET accountValidated = ? WHERE userName = ?";

        $db->executeSql($sql, "si", array(1,$user->userName));

        return new DBResponse(null, $db->lastError);
    }

}