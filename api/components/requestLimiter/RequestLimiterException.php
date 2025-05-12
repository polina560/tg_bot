<?php

namespace api\components\requestLimiter;

use Exception;
use yii\web\HttpException;

/**
 * Class RequestLimiterException
 *
 * @package api\components\requestLimiter
 */
class RequestLimiterException extends HttpException
{
    public function __construct(int $status = 429, string $message = null, int $code = 0, Exception $previous = null)
    {
        parent::__construct($status, $message, $code, $previous);
    }
}
