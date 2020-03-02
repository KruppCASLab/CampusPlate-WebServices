<?php


class Response {
  public $data, $status, $error;

  public function __construct($data = null, $error = null, $status = 0) {
    if ($data !== null) {
      $this->data = $data;
    }
    if ($error !== null) {
      $this->error = $error;
      $this->status = 1;
    }
  }

}