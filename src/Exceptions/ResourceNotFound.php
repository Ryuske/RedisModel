<?php

namespace Ryuske\RedisModel\Exceptions;

use \Exception;

/**
 * Class ResourceNotFound
 * @package Ryuske\RedisModel\Exceptions
 */
class ResourceNotFound extends Exception
{
    /**
     * ResourceNotFound constructor.
     *
     * @param string $message
     * @param int $code
     * @param Exception|NULL $previous
     */
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}