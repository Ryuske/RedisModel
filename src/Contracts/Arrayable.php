<?php

namespace Ryuske\RedisModel\Contracts;

/**
 * @package Ryuske\RedisModel\Contracts
 */
interface Arrayable
{

    /**
     * Implements a method to convert the object to an array
     *
     * @return mixed
     */
    public function toArray();
}