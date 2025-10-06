<?php

class Config {
    static private function loadConfig() {
        //prod
        $config = parse_ini_file("/var/www/cp/config.cfg", true);
        //dev
        //$config = parse_ini_file("/home/nsq5/cp/config.cfg", true);
        return $config;
    }

    static public function getConfigValue($stanza, $key) {
        $config = self::loadConfig();
        return $config[$stanza][$key];
    }

}


