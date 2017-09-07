<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/7/17
 * Time: 10:58
 */

namespace App\Exceptions;

use Exception;

class CustomException extends Exception
{
    /**
     * 自定义的异常类，用于返回控制器错误信息
     * @param string $message
     */
    public function __construct($message = '')
    {
        parent::__construct($message);
    }
}