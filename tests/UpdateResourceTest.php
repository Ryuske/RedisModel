<?php

require_once('Helpers/CreateTestResource.php');

/**
 * Class UpdateResourceTest
 */
class UpdateResourceTest extends TestCase
{

    /**
     * @var
     */
    protected $testingResource;

    /**
     * Test updating a resource
     *
     * @return void
     */
    public function testUpdate()
    {
        $this->testingResource = new CreateTestResource();

        $testModel = app('TestModel');
        $testModel->update($this->testingResource->user->id, [
            'email' => 'kenyon.jh@gmail.com'
        ]);
        $testResource = $testModel->get($this->testingResource->user->id);

        $this->testingResource->userData['email']       = 'kenyon.jh@gmail.com';
        $this->testingResource->testModelSearchableId   = "testmodel:{$this->testingResource->user->id}:{$this->testingResource->user->id}_kenyon.jh@gmail.com_{$this->testingResource->convertedName}_{$this->testingResource->userData['password']}";

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
