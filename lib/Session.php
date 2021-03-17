<?php
Session::startSession();

class Session {
    static public function startSession() {
        session_start();
    }

    static public function setSessionUserId($userId) {
        $_SESSION["userId"] = $userId;
    }

    static public function getSessionUserId() {
        return $_SESSION["userId"];
    }

    static public function isSessionValid(): bool {
        if (isset($_SESSION["userId"]) && $_SESSION["userId"] >= 0) {
            return true;
        }
        return false;
    }

    static public function destroySession() {
        $_SESSION["userId"] = null;
        session_unset();
        session_destroy();
    }
}