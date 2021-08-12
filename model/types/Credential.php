<?php
require_once(__DIR__ . "/Base.php");

class Credential extends Base {
    public $credentialId, $userId, $type, $password, $status, $pin, $label, $created, $lastUsed;

    public function __construct($sourceObject) {
        parent::__construct($sourceObject);
    }
}