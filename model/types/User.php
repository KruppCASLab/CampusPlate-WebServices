<?php

require_once(__DIR__ . "/Base.php");

class User extends Base {

  public $userId, $userName, $password, $role, $pin, $accountValidated;

  public function __construct($sourceObject) {
    parent::__construct($sourceObject);
  }

}