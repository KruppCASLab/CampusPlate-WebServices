<?php

require_once(__DIR__ . "/Base.php");

class User extends Base {
    public $userId, $userName, $role, $credential;

    public function __construct($sourceObject = null) {
        parent::__construct($sourceObject);
    }

}