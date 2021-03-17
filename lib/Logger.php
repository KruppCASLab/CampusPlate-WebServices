<?php

class Logger {
    /**
     * Logs an error message from a system of origin to the system log
     * @param string $origin
     * @param string $errorMessage
     */
    static function log(string $origin, string $errorMessage) {
        $message = "CampusPlate Error: Origin=$origin, Message=$errorMessage";
        error_log($message);
    }
}