<?php

require_once(__DIR__ . "/../model/types/User.php");
require_once(__DIR__ . "/../model/types/Credential.php");
require_once(__DIR__ . "/../model/UsersModel.php");
require_once(__DIR__ . "/../lib/Mail.php");
require_once(__DIR__ . "/../lib/Security.php");
require_once(__DIR__ . "/../lib/Config.php");
require_once(__DIR__ . "/../model/types/Response.php");
require_once(__DIR__ . "/../model/types/Request.php");


class UsersController {
    static public function get(Request $request): Response {
        if ($request->param == "all") {
            $users = UsersModel::getAllUsers();
            return new Response($users);
        }
        else if ($request->param = "all_managers") {
            $managers = UsersModel::getFoodStopManagers();
            return new Response($managers);
        }
        else {
            $user = UsersModel::getUser($request->userId);
            return new Response($user);
        }
    }

    /**
     * Creates a user account, overwrites those that exist with a new pin and invalidates account
     * @param Request $request
     * @return Response status = 0 on successful creation, 1 on failure, 2 on user existed but pin was update
     */
    static public function post(Request $request): Response {
        $user = new User($request->data);
        $credential = new Credential($user->credential);

        $user->userId = $request->userId;
        $credential->pin = Security::getRandomPin();

        $status = 0;

        // Make sure they are at BW by checking if the address ends with ab @bw email
        if (preg_match("/.*?@bw\.edu/", $user->userName) === 0) {
            return new Response(null, null, 3);
        }

        // If user exists, create a new credential, otherwise, create a new user and credential
        $userId = UsersModel::getUserId($user->userName);

        if ($userId != -1) {
            $credential->userId = $userId;

            // Check if we are looking to create a web credential first and one does not already exist
            // We don't want to create more than one web credential
            if ($credential->type == 1 && UsersModel::getWebCredential($credential->userId) != null) {
                // Update the pin
                $webcred = UsersModel::getWebCredential($credential->userId);
                if ($webcred != null) {
                    $webcred = new Credential($webcred);
                    UsersModel::updatePin($webcred->credentialId, $credential->pin);
                }
            }
            else {
                UsersModel::createCredential($credential);
                $status = 2;
            }
        }
        else {
            $userId = UsersModel::createUser($user);

            // Check if we had an error, otherwise, create the credential
            if ($userId == -1) {
                $status = 1;
            }
            else {
                // Take the userId that was created and create the credential
                $credential->userId = $userId;
                UsersModel::createCredential($credential);
            }
        }

        // If we created the user or updated their pin, send an email
        if ($status == 0 || $status == 2) {
            // Previously was unable to use built in Mail library due to IT blocking outgoing mail since Spring '21 Cyberattack
            // In place, using service at home and calling that from this.
            // $url = 'https://krupp.dev/food/mail.php';

//            $data["key"] = Config::getConfigValue("email", "appkey");
//            $data["email"] = $user->userName;
//            $data["pin"] = $credential->pin;

//            $options = array(
//                'http' => array(
//                    'header' => "Content-type: application/json\r\n",
//                    'method' => 'POST',
//                    'content' => json_encode($data)
//                )
//            );
//            $context = stream_context_create($options);
//            file_get_contents($url, false, $context);
            Mail::sendPinEmail($user->userName, $credential->pin);
        }

        return new Response(null, null, $status);;
    }

    /**
     * Verifies a user account with a given pin
     * @param Request $request
     * @return Response status 0 on success, 1 if pin not sent, 2 indicates invalid user/pin match
     */
    static public function patch(Request $request) {
        $userName = $request->id;
        $userId = UsersModel::getUserId($userName);

        $credential = new Credential($request->data);
        $pin = $credential->pin;

        $response = new Response();

        if ($pin != null) {
            $credentialId = UsersModel::verifyPin($userId, $pin);
            if ($credentialId != -1) {
                UsersModel::updateVerifiedFlag($credentialId, true);

                $GUID = Security::generateGUID();
                UsersModel::setPassword($credentialId, $GUID);

                $data["GUID"] = $GUID;
                $response->data = $data;
                $response->status = 0;
                return $response;
            }
            else {
                return new Response(null, null, 2); // Use 2 to indicate invalid match
            }
        }
        else {
            return new Response(null, null, 1); // Use 1 to indicate they did not send pin
        }
    }
}