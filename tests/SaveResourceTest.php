<?php

require_once('Helpers/CreateTestResource.php');

/**
 * Class SaveResourceTest
 */
class SaveResourceTest extends TestCase
{

    /**
     * @var
     */
    protected $testingResource;

    /**
     * Test saving a resource
     *
     * @return void
     */
    public function testSave()
    {
        $this->testingResource = new CreateTestResource();

        $testModel      = app('TestModel');
        $testResource   = $testModel->get($this->testingResource->user->id);

        $testResource->email                            = 'kenyon.jh@gmail.com';
        $this->testingResource->userData['email']       = 'kenyon.jh@gmail.com';
        $this->testingResource->testModelSearchableId   = "testmodel:{$testResource->id}:{$testResource->id}_{$testResource->email}_{$this->testingResource->convertedName}_{$this->testingResource->userData['password']}";

        $testResource->save();

        $this->assertEquals('kenyon.jh@gmail.com', $testResource->email);
        $this->assertArraySubset($this->testingResource->userData, Redis::hgetall($this->testingResource->testModelHashId));
        $this->assertEquals($this->testingResource->user->id, Redis::get($this->testingResource->testModelSearchableId));
    }

    /**
     * Delete the keys that were added to the database during the test
     */
    public function tearDown() {
        parent::tearDown();

        Redis::del($this->testingResource->testModelHashId);
        Redis::del($this->testingResource->testModelSearchableId);
        Redis::del("testmodels");
    }
}
