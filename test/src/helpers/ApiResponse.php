<?php

namespace App\helpers;

class ApiResponse
{
    public function __construct($data, $message)
    {
        $this->data = $data;
        $this->message = $message;
    }

    public function OnSuccess()
    {
        $obj = new \stdClass();
        $obj->status = "success";
        $obj->data = $this->data;
        $obj->message = $this->message;

        return $obj;
    }

    public function OnError()
    {
        $obj = new \stdClass();
        $obj->status = "error";
        $obj->data = null;
        $obj->message = $this->message;
        return $obj;
    }


}