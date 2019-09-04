<?php

class GeoFence {
  static public function onCampus($latitude, $longitude) {
    if ($latitude < 41.388906 && $latitude > 41.357462) {
      if ($longitude > -81.860226 && $longitude < -81.828030) {
        return true;
      }
    }
    return false;
  }
}

?>
