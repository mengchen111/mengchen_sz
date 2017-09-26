<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/26/17
 * Time: 12:23
 */

namespace App\Exceptions;

use Exception;

class GameServerException extends Exception
{
    protected $code;

    public function __construct($message = '', Exception $previous = null)
    {
        $this->code = config('exceptions.GameServerException', 0);
        parent::__construct($message, $this->code, $previous);
    }
}