<?php

namespace Ryuske\RedisModel;

use Ryuske\RedisModel\Contracts\Arrayable;
use Ryuske\RedisModel\Exceptions\IncompatibleParentClass;

/**
 * Class Collection
 * @package Ryuske\RedisModel
 */
abstract class DataAccessor implements Arrayable
{

    /**
     * @param $id
     * @param $keyValueArray
     *
     * @return mixed
     */
    abstract public function update($id, $keyValueArray);

    /**
     * @return mixed
     */
    abstract public function allAvailableFields();

    /**
     * @return mixed
     */
    abstract public function idFieldName();


    /**
     * Used to get a specific field from the given resource
     *
     * @param $field
     *
     * @return mixed
     * @throws IncompatibleParentClass
     */
    public function __get($field)
    {
        $this->checkForNecessaryProperties();

        $unmodifiedField = lcfirst(str_replace('unmodified', '', $field));

        if (!array_key_exists($unmodifiedField, $this->data)) {
            return NULL;
        }

        $attributeMutator = 'get' . str_replace('_', '', ucwords($field, '_')) . 'Attribute';
        return (method_exists($this, $attributeMutator)) ? call_user_func([$this, $attributeMutator]) : $this->data[$unmodifiedField];
    }


    /**
     * Used to set the value of a specific field on the given resource
     *
     * @param $field
     * @param $value
     *
     * @throws IncompatibleParentClass
     */
    public function __set($field, $value)
    {
        $this->checkForNecessaryProperties();

        $this->data[$field] = $value;
    }

    /**
     * Get a specific attribute, without going through any mutators or anything
     * Pretty sure this needs to move to the redis DataAccessor
     *
     * @param $field
     *
     * @return null
     */
    protected function getAttribute($field)
    {
        $this->checkForNecessaryProperties();

        if (!array_key_exists($field, $this->data)) {
            return NULL;
        }

        return $this->data[$field];
    }

    /**
     * Returns an array of the resource
     *
     * @return null|array
     */
    public function toArray() {
        return $this->data;
    }


    /**
     * Used to save the current collection to the database
     *
     * @throws IncompatibleParentClass
     */
    public function save()
    {
        $this->checkForNecessaryProperties();

        $this->update($this->getCurrentId(), $this->data);
    }

    /**
     * Get the ID of the current collection
     *
     * @return null|integer
     */
    protected function getCurrentId()
    {
        return (array_key_exists($this->idfieldName(), $this->data)) ? $this->data[$this->idfieldName()] : NULL;
    }

    /**
     * Makes sure all the necessary properties have been set
     *
     * @throws IncompatibleParentClass
     */
    protected function checkForNecessaryProperties()
    {
        if (!property_exists($this, 'data')) {
            throw new IncompatibleParentClass('Parent class must implement $data property.');
        }
    }

    /**
     * Allows you to use the given resource object as a string
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }

}