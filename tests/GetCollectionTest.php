<?php

require_once('Helpers/CreateTestResource.php');

/**
 * Class GetCollectionTest
 */
class GetCollectionTest extends TestCase
{

    /**
     * @var
     */
    protected $testingResource;

    /**
     * @var
     */
    protected $testingResource2;

    /**
     * Test returning a collection via searchByWildcard
     *
     * @return void
     */
    public function testGetCollection()
    {
        $this->testingResource  = new CreateTestResource([
            'name' => 'Kenyon Haliwell'
        ]);
        $this->testingResource2 = new CreateTestResource([
            'name' => 'Kenyon Smith'
        ]);

        $testModel  = app('TestModel');
        $testingResources = $testModel->searchByWildcard([
            'name' => 'Kenyon*'
        ]);

        // Collections aren't sorted, so which resource is first in the collection is "random"
        $offset1 = ($testingResources[0]->id == $this->testingResource->user->id) ? 0 : 1;
        $offset2 = ($offset1 === 1) ? 0 : 1;

        $this->assertInstanceOf(Ryuske\RedisModel\Collection::class, $testingResources);
        $this->assertEquals($this->testingResource->userData['email'], $testingResources[$offset1]->email);
        $this->assertEquals($this->testingResource2->userData['email'], $testingResources[$offset2]->email);
        $this->assertEquals($testingResources[$offset1]->id, Redis::get($this->testingResource->testModelSearchableId));
        $this->assertEquals($testingResources[$offset2]->id, Redis::get($this->testingResource2->testModelSearchableId));
    }

    /**
     * Test wildcard searching on non-wildcard enabled search function
     *
     * @return void
     */
    public function testWildcardSearchingNonWildcardFunction()
    {
        $this->testingResource  = new CreateTestResource([
            'name' => 'Kenyon Haliwell'
        ]);
        $this->testingResource2 = new CreateTestResource([
            'name' => 'Kenyon Smith'
        ]);

        $testModel  = app('TestModel');
        $testingResources = $testModel->searchBy([
            'name' => 'Kenyon*'
        ]);

        $this->assertNull($testingResources);
    }

    /**
     * Test counting the number of resources in a collection
     *
     * @return void
     */
    public function testCollectionCount()
    {
        $this->testingResource  = new CreateTestResource([
            'name' => 'Kenyon Haliwell'
        ]);
        $this->testingResource2 = new CreateTestResource([
            'name' => 'Kenyon Smith'
        ]);

        $testModel  = app('TestModel');
        $testingResources = $testModel->searchByWildcard([
            'name' => 'Kenyon*'
        ]);

        $this->assertEquals(2, $testingResources->count());
    }

    /**
     * Test returning an empty collection
     *
     * @return void
     */
    public function testEmptyCollection()
    {
        $this->testingResource  = new CreateTestResource([
            'name' => 'Kenyon Haliwell'
        ]);
        $this->testingResource2 = new CreateTestResource([
            'name' => 'Kenyon Smith'
        ]);

        $testModel  = app('TestModel');
        $testingResources = $testModel->searchByWildcard([
            'name' => 'Meow*'
        ]);

        $this->assertNull($testingResources);
    }

    /**
     * Test using a collection as a string
     *
     * @return void
     */
    public function testUsingCollectionAsString()
    {
        $this->testingResource  = new CreateTestResource([
            'name' => 'Kenyon Haliwell'
        ]);
        $this->testingResource2 = new CreateTestResource([
            'name' => 'Kenyon Smith'
        ]);

        $testModel = app('TestModel');
        $testingResources = $testModel->searchByWildcard([
            'name' => 'Kenyon*'
        ]);

        $this->assertJson("$testingResources");
    }

    /**
     * Delete the keys that were added to the database during the test
     */
    public function tearDown() {
        Redis::del($this->testingResource->testModelHashId);
        Redis::del($this->testingResource->testModelSearchableId);
        Redis::del($this->testingResource2->testModelHashId);
        Redis::del($this->testingResource2->testModelSearchableId);
        Redis::del("testmodels");

        parent::tearDown();
    }
}
