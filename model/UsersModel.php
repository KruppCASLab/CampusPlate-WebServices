<?php

require_once(__DIR__ . "/Database.php");
require_once(__DIR__ . "/types/Listing.php");
require_once(__DIR__ . "/types/User.php");
require_once(__DIR__ . "/types/Credential.php");
require_once(__DIR__ . "/types/Response.php");

class UsersModel {
    /**
     * Authenticates user and returns userId
     * @param $username
     * @param $password
     * @return int
     */
    static public function authenticate($username, $password): int {
        $sql = "SELECT * from tblCredentials as credentials INNER JOIN tblUsers as users ON users.userId = credentials.userId WHERE users.userName = ? AND credentials.status = ?";
        $results = Database::executeSql($sql, "si", array($username, 1));

        // If we get nothing back, user does not exist
        if (sizeof($results) == 0) {
            return -1;
        }

        // Go through results and if we have a match, then return the user
        foreach($results as $result) {
            $credential = new Credential($result);
            if (password_verify($password, $credential->password)) {
                self::updateAuthTimestamp($credential->credentialId);
                return $credential->userId;
            }
        }

        // If we get out here, then we could not match
        return -1;
    }

    static public function updateAuthTimestamp($credentialId) {
        $sql = "UPDATE tblCredentials SET lastUsed = ? where credentialId = ?";
        Database::executeSql($sql, "ii", array(time(),$credentialId));
    }

    /**
     * Returns a User given a userId.
     * @param $userId
     * @return User
     */
    static public function getUser($userId): User {
        $sql = "SELECT userId, userName, role, accountValidated, requireReset FROM tblUsers WHERE userId = ?";
        $results = Database::executeSql($sql, "i", array($userId));

        return new User($results[0]);
    }

    static public function getAllUsers(): array {
        $sql = "SELECT userId, userName, role, accountValidated, requireReset FROM tblUsers";
        $results = Database::executeSql($sql);

        return $results;
    }

    static public function getFoodStopManagers(): array {
        $sql = "SELECT users.userId, users.userName, managers.foodStopId, foodStops.name from tblFoodStopManagers as managers INNER JOIN tblUsers as users on managers.userId = users.userId INNER JOIN tblFoodStops as foodStops on foodStops.foodStopId = managers.foodStopId ORDER BY managers.foodStopId";
        $results = Database::executeSql($sql);
        return $results;
    }

    /**
     * Checks if a user exists given a username
     * @param $username
     * @return int userId if user exists, otherwise -1
     */
    static public function getUserId($username): int {
        $sql = "SELECT userId FROM tblUsers WHERE userName = ?";
        $result = Database::executeSql($sql, "s", array($username));
        if (sizeof($result) > 0) {
            return $result[0]["userId"];
        }
        else {
            return -1;
        }
    }

    /**
     * Creates a user give a username and a credential
     * @param User $user
     * @return int userId on success, -1 otherwise
     */
    static public function createUser(User $user): int {
        $sql = "INSERT INTO tblUsers(userName) VALUES (?)";
        $userId = Database::executeSql($sql, "s", array($user->userName));

        if (isset(Database::$lastError)) {
            return -1;
        }
        else {
            return $userId;
        }
    }

    static public function getWebCredential($userId) {
        $sql = "SELECT * from tblCredentials where userId = ? and type = ?";
        $credentials = Database::executeSql($sql, "ii", array($userId, 1));
        if (sizeof($credentials) > 0) {
            return $credentials[0];
        }
        else {
            return null;
        }
    }

    /**
     * @param Credential $credential
     * @return int credentialId on success, -1 otherwise
     */
    static public function createCredential(Credential $credential): int {
        $credential->created = time();
        $credential->lastUsed = time();

        $sql = "INSERT INTO tblCredentials(userId, type, pin, label, created, lastUsed) values (?, ?, ?, ?, ?, ?)";
        $credentialId = Database::executeSql($sql, "iissii", array($credential->userId, $credential->type, $credential->pin, $credential->label,$credential->created, $credential->lastUsed));

        if (isset(Database::$lastError)) {
            return -1;
        }
        else {
            return $credentialId;
        }
    }


    /**
     * Updates the credential with a new pin
     * @param $credentialId
     * @param $pin
     * @return bool
     */
    static public function updatePin($credentialId, $pin): bool {
        $sql = "UPDATE tblCredentials SET pin = ? WHERE credentialId = ?";
        Database::executeSql($sql, "si", array($pin, $credentialId));
        return !isset(Database::$lastError);
    }

    /**
     *
     * @param User $user
     * @return int credentialId if there is a match, otherwise, -1
     */
    static public function verifyPin($userId, $pin): int {
        $db = new Database();
        $sql = "SELECT credentialId FROM tblCredentials WHERE userId = ? AND pin = ?";

        $results = $db->executeSql($sql, "is", array($userId, $pin));

        // Pin and Username combo don't exist
        if (sizeof($results) == 0) {
            return -1;
        }
        else {
            return $results[0]["credentialId"];
        }
    }

    /**
     * Updates the verified flag on a credential
     * @param int $credentialId
     * @param bool $isVerified
     * @return bool true on success, false otherwise
     */
    static public function updateVerifiedFlag(int $credentialId, bool $isVerified): bool {
        $verified = 0;
        if ($isVerified) {
            $verified = 1;
        }

        $sql = "UPDATE tblCredentials SET status = ? WHERE credentialId = ?";
        Database::executeSql($sql, "ii", array($verified, $credentialId));
        return !isset(Database::$lastError);
    }

    static public function setPassword($credentialId, $password) {
        $sql = "UPDATE tblCredentials set password = ? where credentialId = ?";
        Database::executeSql($sql, "si", array(password_hash($password, PASSWORD_DEFAULT), $credentialId));
    }

}