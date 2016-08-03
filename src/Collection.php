<?php

namespace Ryuske\RedisModel;

use Ryuske\RedisModel\Contracts\Arrayable;
use Ryuske\RedisModel\Contracts\CollectionInterface;
use ArrayAccess;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;
use ArrayIterator;

/**
 * Class Collection
 * @package Ryuske\RedisModel
 */
class Collection implements CollectionInterface, ArrayAccess, Arrayable, IteratorAggregate
{

    /**
     * // Returns a collection of users
     * $users = $userModel->searchBy('Kenyon', ['name', 'email']);
     *
     * $users->count();
     * $users[0]->name;
     */

    protected $resources;

    /**
     * Collection constructor.
     *
     * @param $resources
     */
    public function __construct($resources)
    {
        if (!is_array($resources)) {
            throw new InvalidArgumentException(__CLASS__ . ' must be an array of resources.');
        }

        $this->resources = $resources;
    }

    /**
     * Returns the number of resources in a collection
     *
     * @return int
     */
    public function count()
    {
        return count($this->resources);
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->resources);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->resources[$offset];
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->resources[$offset] = $value;
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        unset($this->resources[$offset]);
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->resources);
    }

    /**
     * Allows you to use the collection as a json string
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode(($this->toArray()));
    }

    /**
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new ArrayIterator($this->resources);
    }
}