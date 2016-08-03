<?php

namespace Ryuske\RedisModel\Contracts;

/**
 * Interface CollectionInterface
 * @package Ryuske\RedisModel\Contracts
 */
interface CollectionInterface
{

    /**
     * CollectionInterface constructor.
     *
     * @param $resources
     */
    public function __construct($resources);

    /**
     * Count how many resources are in the collection
     *
     * @return mixed
     */
    public function count();
}