<?php


class Response {
  public $data, $status, $error;

  public function __construct($data = null, $error = null, $status = 0) {
    $this->status = $status;
    $this->data = $data;
    $this->error = $error;

    // If we had an error and the status is 0, we need to overwrite it
    if ($error !== null && $status === 0) {
        $this->status = -1;
    }
  }
}