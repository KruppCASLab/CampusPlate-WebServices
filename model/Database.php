<?php

require_once(__DIR__ . "/../lib/Config.php");

class Database {
  public $lastError;
  private function getDbConnection() {
    $servername = Config::getConfigValue("db", "host");
    $username = Config::getConfigValue("db", "username");
    $password = Config::getConfigValue("db", "password");
    $database = Config::getConfigValue("db", "database");

    $dbConnection = new mysqli($servername, $username, $password, $database);

    if ($dbConnection->connect_errno) {
      die("Unable to connect to DB");
    }

    return $dbConnection;
  }

  public function executeSql($sqlQuery, $bindTypes = null, $bindParams = null) { // Adding null makes them optional
    $this->lastError = null;

    $db = $this->getDbConnection();

    if ($db === false) return false;

    $statement = $db->stmt_init();

    $results = array();

    if (!$statement->prepare($sqlQuery)) {
      $this->lastError = $statement->error . " with " . $sqlQuery;
      return false;
    } else {
      if (isset($bindTypes) && isset($bindParams)) {
        $statement->bind_param($bindTypes, ...$bindParams);
      }
      if ($statement->execute() === false) {
        $this->lastError = $db->error;
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