<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/7/17
 * Time: 10:58
 */

namespace App\Exceptions;

use Exception;

class InventoryServiceException extends Exception
{
    protected $code;

    /**
     * @param string $message
     */
    public function __construct($message = '', Exception $previous = null)
    {
        $this->code = config('exceptions.InventoryServiceException');
        parent::__construct($message, $this->code, $previous);
    }
}