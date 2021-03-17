<?php

require_once(__DIR__ . "/../lib/Config.php");
require_once(__DIR__ . "/../lib/Logger.php");

class Filesystem {


    /**
     * Returns the base directory to store images
     * @return string
     */
    static private function getBaseDir(): string {
        $basedir = Config::getConfigValue("app", "image_dir");
        if (!is_dir($basedir)) {
            if (!mkdir($basedir, 0777)) {
                Logger::log("Filesystem Library", "Unable to make directory for files $basedir");
            }
        }
        return $basedir;
    }

    /**
     * Saves a file given an ID and data
     * @param $id - ID of the image
     * @param $imageData - The actual data
     * @return bool - true on sucess, false otherweise
     */
    static public function saveFile($id, $imageData): bool {
        $basedir = self::getBaseDir();
        $path = $basedir . "/" . $id;
        if (file_put_contents($path, $imageData) === FALSE) {
            Logger::log("Filesystem Library", "Unable to save contents to path $path");
            return false;
        }
        return true;
    }

    /**
     * Gets a file based on the ID of the image
     * @param $id - ID of the image
     * @return mixed - File data
     */
    static public function getFile($id) {
        $basedir = self::getBaseDir();
        $path = $basedir . "/" . $id;
        $data = file_get_contents($path);
        if ($data === FALSE) {
            Logger::log("Filesystem Library", "Unable to get contents from path $path");
        }
        return $data;
    }
}