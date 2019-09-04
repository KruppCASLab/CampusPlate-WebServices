<?php

class Base {
  public function __construct($sourceObject) {
    if (is_object($sourceObject) || is_array($sourceObject)) {
      foreach ($sourceObject as $key => $val) {
        // Make sure that the object has the property before setting it
        if (property_exists($this, $key)) {
          $this->$key = $val;
        }
      }
    }
  }
}