<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");

class UsersModel {

  static public function createUser(User $user): Response {
    $db = new Database();

    $sql = "INSERT INTO tblUsers(userName, password, emailAddress, pin) VALUES (?, ?, ?,?)";

    $db->executeSql($sql, "sssi", array($user->userName, Security::hashPassword($user->password), $user->emailAddress, $user->pin));

    return new Response(null, $db->lastError);
  }

    static public function checkPinAndUser(User $user): Response {
        $db = new Database();

        $sql = "SELECT pin FROM tblUsers WHERE userName = ? AND pin = ?";

        $db->executeSql($sql, "si", array($user->userName, $user->pin));

        //TODO: If a user pin/combo does not exist, it will not be an error, you need to check the size of the results


        return new Response(null, $db->lastError);
    }

    static public function updateVerifiedFlag(User $user): Response {
        $db = new Database();

        $sql = "UPDATE tblUsers SET accountValidated = ? WHERE userName = ?";

        $db->executeSql($sql, "is", array(1,$user->userName));

        return new Response(null, $db->lastError);
    }

}