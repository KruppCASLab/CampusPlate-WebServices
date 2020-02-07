<?php

require_once(__DIR__ . "/Base.php");

class User extends Base {

  public $userId, $username, $password, $emailAddress, $role, $pin, $accountValidated;

  public function __construct($sourceObject) {
    parent::__construct($sourceObject);
  }

}