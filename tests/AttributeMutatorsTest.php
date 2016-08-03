<?php

require_once('Helpers/CreateTestResource.php');

/**
 * Class GetResourceTest
 */
class AttributeMutatorsTest extends TestCase
{

    /**
     * @var
     */
    protected $testingResource;

    /**
     * Test attribute mutators
     *
     * @return void
     */
    public function testAttributeMutator()
    {
        $this->testingResource = new CreateTestResource([
            'phone_number' => '1231231234'
        ]);

        $testModel = app('TestModel');
        $testResource = $testModel->get($this->testingResource->user->id);

        $this->assertEquals('123-123-1234', $testResource->phone_number);
    }

    /**
     * Test getting the regular value of a mutated attribute
     *
     * @return void
     */
    public function testAttributeUnmodified()
    {
        $this->testingResource = new CreateTestResource([
            'phone_number' => 1231231234
        ]);

        $testModel = app('TestModel');
        $testResource = $testModel->get($this->testingResource->user->id);

        $this->assertEquals('1231231234', $testResource->unmodifiedPhone_number);
    }

    /**
     * Delete the keys that were added to the database during the test
     */
    public function tearDown() {
        Redis::del($this->testingResource->testModelHashId);
        Redis::del($this->testingResource->testModelSearchableId);
        Redis::del("testmodels");

        parent::tearDown();
    }
}
