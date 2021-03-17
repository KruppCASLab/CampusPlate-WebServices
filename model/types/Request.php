<?php

class Request {
    public $data, $id, $param, $userId;

    /**
     * Request constructor.
     * @param $data object data that will be return to client
     * @param $id int unique identifier for request
     * @param $param string additional params to identify which part of object to update
     * @param $userId int the user making the request
     */
    public function __construct($data = null, int $id = null, string $param = null, int $userId = null) {
        $this->data = $data;
        $this->id = $id;
        $this->param = $param;
        $this->userId = $userId;
    }
}