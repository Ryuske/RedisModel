<?php

use Ryuske\RedisModel\Model;

/**
 * Class TestModel
 */
class TestModel extends Model
{
    /**
     * @var array
     */
    protected $indexes = [
        'id',
        'email',
        'name',
        'password'
    ];

    /**
     * @var array
     */
    protected $fields = [
        'phone_number',
        'address'
    ];

    /**
     * @var array
     */
    protected $hiddenFields = [
        'password'
    ];

    /**
     * Attribute mutator for phone number.
     * Outputs format: 123-123-1234
     *
     * @return string
     */
    protected function getPhoneNumberAttribute()
    {
        if (!array_key_exists('phone_number', $this->data) || empty($this->data['phone_number'])) {
            return NULL;
        }

        $x = $this->data['phone_number'];
        return "{$x[0]}{$x[1]}{$x[2]}-{$x[3]}{$x[4]}{$x[5]}-{$x[6]}{$x[7]}{$x[8]}{$x[9]}";
    }

    /**
     * Return the original phone number, without the mutator
     *
     * @return string|null
     */
    protected function getUnmodifiedPhoneNumberAttribute()
    {
        return $this->getAttribute('phone_number');
    }
}