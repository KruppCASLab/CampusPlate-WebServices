<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");

class UsersModel {

  static public function createUser(User $user): Response {
    $db = new Database();

    $sql = "INSERT INTO tblUsers(username, password, emailAddress) VALUES (?, ?, ?)";

    $db->executeSql($sql, "sss", array($user->username, Security::hashPassword($user->password), $user->emailAddress));

    return new Response(null, $db->lastError);
  }

}