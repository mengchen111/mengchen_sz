<?php

namespace App\Exceptions;

use Exception;

class RoomStatementServiceException extends Exception
{
    protected $code;

    public function __construct($message = '', Exception $previous = null)
    {
        $this->code = config('exceptions.RoomStatementServiceException', 0);
        parent::__construct($message, $this->code, $previous);
    }
}