<?php

namespace App\Exceptions;

use Exception;

/**
 * @SWG\Definition(
 *     definition="GameApiServiceError",
 *     type="object",
 *     @SWG\Property(
 *         property="result",
 *         description="结果(false)",
 *         type="boolean",
 *         default=false,
 *     ),
 *     @SWG\Property(
 *         property="code",
 *         description="返回码，大于等于0",
 *         type="integer",
 *         format="int32",
 *         example="2000",
 *     ),
 *     @SWG\Property(
 *         property="error",
 *         description="调用游戏后端接口报错信息",
 *         type="string",
 *         example="调用游戏后端接口报错信息",
 *     ),
 * ),
 */
class GameApiServiceException extends Exception
{
    public function __construct($message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}