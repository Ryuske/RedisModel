<?php

namespace Ryuske\RedisModel\Contracts;


/**
 * Interface RepositoryInterface
 * @package Ryuske\RedisModel\Contracts
 */
interface RepositoryInterface
{

    /**
     * @param $id
     * @param $fields
     *
     * @return mixed
     */
    public function get($id, $fields);

    /**
     * @param $keyValueArray
     *
     * @return mixed
     */
    public function create($keyValueArray);

    /**
     * @param $id
     * @param $keyValueArray
     *
     * @return mixed
     */
    public function update($id, $keyValueArray);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function delete($id);
}