<?php

require_once('TestModel.php');

/**
 * Class CreateTestResource
 */
class CreateTestResource {

    /**
     * @var array
     */
    public $userData;

    /**
     * @var
     */
    protected $data;

    /**
     * CreateTestResource constructor.
     *
     * @param array $values
     */
    public function __construct($values = [])
    {
        $faker = Faker\Factory::create();

        $testModel  = App::make('TestModel');
        $this->userData   = [
            'email'        => (array_key_exists('email', $values)) ? $values['email'] : $faker->email,
            'name'         => (array_key_exists('name', $values)) ? $values['name'] : $faker->name,
            'phone_number' => (array_key_exists('phone_number', $values)) ? $values['phone_number'] : $faker->phoneNumber,
            'address'      => (array_key_exists('address', $values)) ? $values['address'] : $faker->address,
            'password'     => (array_key_exists('password', $values)) ? $values['password'] : 'wtf',
        ];

        $this->convertedName            = strtolower(str_replace(' ', '+', $this->userData['name']));
        $this->user                     = $testModel->create($this->userData);
        $this->testModelHashId          = "testmodel:{$this->user->id}";
        $this->testModelSearchableId    = "testmodel:{$this->user->id}:{$this->user->id}_{$this->userData['email']}_{$this->convertedName}_{$this->userData['password']}";
    }

    /**
     * @param $key
     *
     * @return null
     */
    public function __get($key)
    {
        return (array_key_exists($key, $this->data)) ? $this->data[$key] : NULL;
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
}