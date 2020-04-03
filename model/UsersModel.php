<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");

class UsersModel {

  static public function createUser(User $user): Response {
    $db = new Database();

    $sql = "INSERT INTO tblUsers(userName, password, pin) VALUES (?, ?, ?)";

    $db->executeSql($sql, "ssi", array($user->userName, Security::hashPassword($user->password), $user->pin));

    return new Response(null, $db->lastError);
  }

    static public function checkPinAndUser(User $user): Response {
        $response = new Response();
        $db = new Database();

        $sql = "SELECT pin FROM tblUsers WHERE userName = ? AND pin = ?";

        $results = $db->executeSql($sql, "si", array($user->userName, $user->pin));

        // Pin and Username combo don't exist
        if (sizeof($results) == 0) {
          $response->status = 1;
        }
        else {
          $response->status = 0;
        }

        return $response;
    }

    static public function updateVerifiedFlag(User $user): Response {
        $db = new Database();

        $sql = "UPDATE tblUsers SET accountValidated = ? WHERE userName = ?";

        $db->executeSql($sql, "is", array(1,$user->userName));

        return new Response(null, $db->lastError);
    }

    static public function addGuid(User $user, $GUID): Response{
      $db = new Database();

      $sql = "UPDATE tblUsers SET GUID = ? WHERE userName = ?";

      $db->executeSql($sql,"ss", array($GUID,$user->userName));

      return new Response(null, $db->lastError);
    }



}