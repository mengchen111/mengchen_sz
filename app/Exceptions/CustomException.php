<?php
/**
 * Created by PhpStorm.
 * User: liudian
 * Date: 9/7/17
 * Time: 10:58
 */

namespace App\Exceptions;

use Exception;

/**
 * @SWG\Definition(
 *     definition="CommonError",
 *     type="object",
 *     @SWG\Property(
 *         property="code",
 *         description="返回码，大于等于0",
 *         type="integer",
 *         format="int32",
 *         example="1000",
 *     ),
 *     @SWG\Property(
 *         property="error",
 *         description="错误消息提示",
 *         type="string",
 *         example="原密码错误",
 *     ),
 * ),
 */
class CustomException extends Exception
{
    protected $code;

    /**
     * 自定义的异常类，用于返回error json响应
     * @param string $message
     */
    public function __construct($message = '', Exception $previous = null)
    {
        $this->code = config('exceptions.CustomException', 0);
        parent::__construct($message, $this->code, $previous);
    }
}