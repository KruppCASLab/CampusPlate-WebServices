<?php


class DBResponse {
  public $data, $status = 0, $error;

  public function __construct($data, $error) {
    if ($data !== null) {
      $this->data = $data;
    }
    if ($error !== null) {
      $this->error = $error;
      $this->status = 1;
    }
  }

}