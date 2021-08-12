<?php
require_once(__DIR__ . "/Config.php");
require_once(__DIR__ . "/../model/UsersModel.php");
require_once(__DIR__ . "/../model/types/User.php");

class Security {

    /**
     * Determines if the user need to authenticate when accessing a specific service
     * @param $resource
     * @param $method
     * @return bool
     */
    static function isAuthenticationRequired($resource, $method): bool {
        if ($resource == "users" && ($method == "post" || $method == "patch")) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Authenticates a user given a username and a password, returns valid userId or -1 otherwise
     * @param $username
     * @param $password
     * @return int
     */
    static function authenticateUser($username, $password): int {
        return UsersModel::authenticateUser($username, $password);
    }

    /**
     * @param $username
     * @param $password
     */
    static function resetPassword($username, $password) {
        $user = new User(null);
        $user->userName = $username;
        UsersModel::updateVerifiedFlag($user, true);
        UsersModel::setPassword($username, $password);
    }

    /**
     * Generates random pin from 100000 to 999999
     * @return int
     */
    static public function getRandomPin() {
        return mt_rand(100000, 999999);

    }

    /**
     * Generates GUID
     * @return string
     */
    static public function generateGUID() {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    /**
     * @param $username
     * @param $pin
     * @return bool
     */
    static public function verifyUserPin($username, $pin) {
        $userId = UsersModel::getUserId($username);
        return UsersModel::verifyPin($userId, $pin);
    }


    /**
     * Removes XSS attacks from input
     * @param array $input
     * @return array Array data will have HTML and JavaScript attacks removed
     */
    static public function sanitizeArrayInput(array $input) : array {
        $sanitized = array();
        foreach($input as $element) {
            array_push($sanitized, htmlentities($element));
        }
        return $sanitized;
    }


}
