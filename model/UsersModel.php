<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Response.php");

class UsersModel {
    /**
     * Authenticates user device and returns userId
     * @param $username
     * @param $deviceToken
     * @return int userId of user, -1 if authentication failed
     */
    static public function authenticateUserDevice($username, $deviceToken): int {
        $sql = "SELECT userId FROM tblUsers WHERE userName = ? AND GUID = ?";
        $results = Database::executeSql($sql, "ss", array($username, $deviceToken));
        if (sizeof($results) > 0) {
            $user = new User($results[0]);
            return $user->userId;
        }
        else {
            return -1;
        }
    }

    /**
     * Authenticates user and returns userId
     * @param $username
     * @param $password
     * @return int
     */
    static public function authenticateUser($username, $password): int {
        $sql = "SELECT userId, password FROM tblUsers WHERE userName = ? AND accountValidated = ?";
        $results = Database::executeSql($sql, "si", array($username, 1));

        // If we get nothing back, user does not exist
        if (sizeof($results) == 0) {
            return -1;
        }
        $user = new User($results[0]);

        if (password_verify($password, $user->password)) {
            return $user->userId;
        }
        else {
            return -1;
        }
    }


    static public function setPassword($username, $password) {
        $sql = "UPDATE tblUsers set password = ? where userName = ?";
        Database::executeSql($sql, "ss", array(password_hash($password, PASSWORD_DEFAULT), $username));
    }


    /**
     * Returns a User given a userId.
     * @param $userId
     * @return User
     */
    static public function getUser($userId): User {
        $sql = "SELECT userId, userName, role, accountValidated, requireReset FROM tblUsers WHERE userId = ?";
        $results = Database::executeSql($sql, "d", array($userId));

        return new User($results[0]);
    }

    /**
     * Checks if a user exists given a username
     * @param $username
     * @return bool true if user exists, false otherwise
     */
    static public function doesUserExist($username): bool {
        $sql = "SELECT userId FROM tblUsers WHERE userName = ?";
        $result = Database::executeSql($sql, "s", array($username));
        return sizeof($result) > 0;
    }

    /**
     * Creates a user give a username and a pin
     * @param User $user
     * @return bool true on success, false otherwise
     */
    static public function createUser(User $user): bool {
        $sql = "INSERT INTO tblUsers(userName, pin) VALUES (?, ?)";
        Database::executeSql($sql, "ss", array($user->userName, $user->pin));
        return !isset(Database::$lastError);
    }

    /**
     * Updates a user pin to a new value
     * @param User $user
     * @return bool true on success, false otherwise
     */
    static public function updatePin(User $user): bool {
        $sql = "UPDATE tblUsers SET pin = ? WHERE userName = ?";
        Database::executeSql($sql, "ss", array($user->pin, $user->userName));
        return !isset(Database::$lastError);
    }

    /**
     *
     * @param User $user
     * @return bool true if uusername and pin combination exists, false otherwise
     */
    static public function verifyPin(User $user): bool {
        $response = new Response();
        $db = new Database();

        $sql = "SELECT userId FROM tblUsers WHERE userName = ? AND pin = ?";

        $results = $db->executeSql($sql, "si", array($user->userName, $user->pin));

        // Pin and Username combo don't exist
        if (sizeof($results) == 0) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Sets whether or not a user is verified.
     * @param User $user
     * @param bool $isVerified
     * @return bool true on success, false otherwise
     */
    static public function updateVerifiedFlag(User $user, bool $isVerified): bool {
        $verified = 0;
        if ($isVerified) {
            $verified = 1;
        }

        $sql = "UPDATE tblUsers SET accountValidated = ? WHERE userName = ?";
        Database::executeSql($sql, "is", array($verified, $user->userName));
        return !isset(Database::$lastError);
    }

    /**
     * Sets the GUID for a given user
     * @param User $user
     * @param $GUID
     * @return bool true on success, false otherwise
     */
    static public function setGUID(User $user, $GUID): bool {
        $sql = "UPDATE tblUsers SET GUID = ? WHERE userName = ?";
        Database::executeSql($sql, "ss", array($GUID, $user->userName));
        return !isset(Database::$lastError);
    }

}