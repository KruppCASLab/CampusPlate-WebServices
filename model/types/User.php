<?php

require_once(__DIR__ . "/Base.php");

class User extends Base {

    public $userId, $userName, $role, $pin, $accountValidated, $GUID, $password;

    public function __construct($sourceObject) {
        parent::__construct($sourceObject);
    }

}