<?php

require_once('Helpers/CreateTestResource.php');

/**
 * Class CreateResourceTest
 */
class CreateResourceTest extends TestCase
{

    /**
     * @var
     */
    protected $testingResource;

    /**
     * Test the creation of a resource
     *
     * @return void
     */
    public function testCreate()
    {
        $this->testingResource = new CreateTestResource();

        $this->assertInstanceOf(TestModel::class, $this->testingResource->user);
        $this->assertEquals($this->testingResource->userData['email'], $this->testingResource->user->email);
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
