<?php

require_once('Helpers/CreateTestResource.php');

/**
 * Class DeleteResourceTest
 */
class DeleteResourceTest extends TestCase
{

    /**
     * Test the deletion of a resource from within the context of a resource
     *
     * @return void
     */
    public function testDeleteFromResourceContext()
    {
        $testingResource = new CreateTestResource();
        $testingResource->user->delete();

        $this->assertEmpty(Redis::hgetall($testingResource->testModelHashId));
        $this->assertNull(Redis::get($testingResource->testModelSearchableId));
    }

    /**
     * Test the deletion of a resource from within the context of a collection
     *
     * @return void
     */
    public function testDeleteFromCollectionContext()
    {
        $testingResource  = new CreateTestResource([
            'name' => 'Kenyon Haliwell'
        ]);
        $testingResource2 = new CreateTestResource([
            'name' => 'Kenyon Smith'
        ]);

        $testModel  = app('TestModel');
        $testingResources = $testModel->searchByWildcard([
            'name' => 'Kenyon*'
        ]);

        $testingResources[1]->delete();
        $testingResources[0]->delete();

        $this->assertEmpty(Redis::hgetall($testingResource2->testModelHashId));
        $this->assertNull(Redis::get($testingResource2->testModelSearchableId));

        $this->assertEmpty(Redis::hgetall($testingResource->testModelHashId));
        $this->assertNull(Redis::get($testingResource->testModelSearchableId));
    }

    /**
     * Test the deletion of a resource by ID
     *
     * @return void
     */
    public function testDeleteById()
    {
        $testingResource = new CreateTestResource();

        $testModel = app('TestModel');
        $testModel->delete($testingResource->user->id);

        $this->assertEmpty(Redis::hgetall($testingResource->testModelHashId));
        $this->assertNull(Redis::get($testingResource->testModelSearchableId));
    }

    /**
     * Delete the keys that were added to the database during the test
     */
    public function tearDown()
    {
        parent::tearDown();

        Redis::del("testmodels");
    }
}
