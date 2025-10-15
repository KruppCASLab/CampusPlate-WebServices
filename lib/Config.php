<?php

class Config {
    static private $path = __DIR__ . "/../config.cfg";
    static private function loadConfig() {
        $config = parse_ini_file(Config::$path, true);
        return $config;
    }

    static public function getConfigValue($stanza, $key) {
        $config = self::loadConfig();
        return $config[$stanza][$key];
    }

}


