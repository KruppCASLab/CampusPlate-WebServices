<?php

require_once(__DIR__ . "/../lib/Config.php");
require_once(__DIR__ . "/../lib/Logger.php");

class Database {
  static public $lastError;
  static private $db;

  static private function getDbConnection() {
    $servername = Config::getConfigValue("db", "host");
    $username = Config::getConfigValue("db", "username");
    $password = Config::getConfigValue("db", "password");
    $database = Config::getConfigValue("db", "database");

    $dbConnection = new mysqli($servername, $username, $password, $database);

    if ($dbConnection->connect_errno) {
      self::handleError($dbConnection->error, "Unable to connect to DB");
      die();
    }

    return $dbConnection;
  }

  static private function handleError($error, $message) {
    Database::$lastError = $error;
    Logger::log(Database::class, "Error: $error, Detail: $message");
  }

  static public function executeSql($sqlQuery, $bindTypes = null, $bindParams = null) { // Adding null makes them optional
    Database::$lastError = null;
    $db = self::getDbConnection();

    if ($db === false) return false;

    $statement = $db->stmt_init();

    $results = array();

    if (!$statement->prepare($sqlQuery)) {
      Database::$lastError = $statement->error . " with " . $sqlQuery;
      self::handleError($statement->error, "Failed on Prepare SQL: " . $sqlQuery);
      return false;
    } else {
      if (isset($bindTypes) && isset($bindParams)) {
        $statement->bind_param($bindTypes, ...$bindParams);
      }
      if ($statement->execute() === false) {
        Database::$lastError = $db->error;
        self::handleError($db->error, "Failed on Execute SQL: " . $sqlQuery);
        return false;
      }

      $result = $statement->get_result();
      // Check if this is returning data
      if ($result) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
          array_push($results, $row);
        }
      } else {
        // Check if there is an ID for the insert, if so return it
        if ($db->insert_id != null) {
          $results = $db->insert_id;
        }
      }
    }

    $statement->close();
    $db->close();

    return $results;
  }
}