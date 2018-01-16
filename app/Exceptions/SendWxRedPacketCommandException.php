<?php

namespace App\Exceptions;

use Exception;

class SendWxRedPacketCommandException extends Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        if (empty($code)) {
            $code = config('exceptions.SendWxRedPacketCommandException', 0);
        }
        parent::__construct($message, $code, $previous);
    }
}