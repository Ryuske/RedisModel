<?php

require_once('Helpers/CreateTestResource.php');

/**
 * Class GetResourceTest
 */
class GetResourceTest extends TestCase
{

    /**
     * @var
     */
    protected $testingResource;

    /**
     * Test getting a resource by id
     *
     * @return void
     */
    public function testGet()
    {
        $this->testingResource = new CreateTestResource();

        $testModel = App::make('TestModel');
        $testResource = $testModel->get($this->testingResource->user->id);

        $this->assertInstanceOf(TestModel::class, $testResource);
        $this->assertEquals($this->testingResource->userData['email'], $testResource->email);
    }

    /**
     * Test getting a single resource by searching
     *
     * @return void
     */
    public function testGettingSingleSearchBySearching()
    {
        $this->testingResource = new CreateTestResource([
            'email' => 'test@example.com'
        ]);

        $testModel = App::make('TestModel');
        $testResource = $testModel->searchBy([
            'email' => 'test@example.com'
        ]);

        $this->assertInstanceOf(TestModel::class, $testResource);
        $this->assertEquals($this->testingResource->userData['email'], $testResource->email);
    }

    /**
     * Test getting an invalid resource
     *
     * @return void
     */
    public function testGettingInvalidResource()
    {
        $this->testingResource = new CreateTestResource();

        $testModel = app('TestModel');
        $testResource = $testModel->get(7);

        $this->assertNull($testResource);
    }

    /**
     * Test getting a resource by getOrFail
     *
     * @return void
     */
    public function testGetOrFail()
    {
        $this->testingResource = new CreateTestResource();

        $testModel = app('TestModel');
        $testResource = $testModel->getOrFail($this->testingResource->user->id);

        $this->assertInstanceOf(TestModel::class, $testResource);
        $this->assertEquals($this->testingResource->userData['email'], $testResource->email);
    }

    /**
     * Test a failed getOrFail
     *
     * @return void
     */
    public function testFailingGetOrFail()
    {
        $this->setExpectedException('Ryuske\RedisModel\Exceptions\ResourceNotFound');

        $this->testingResource = new CreateTestResource();

        $testModel = app('TestModel');
        $testModel->getOrFail(7);
    }

    /**
     * Test using a resource as a string
     *
     * @return void
     */
    public function testUsingResourceAsString()
    {
        $this->testingResource = new CreateTestResource();

        $testModel = app('TestModel');
        $testResource = $testModel->get($this->testingResource->user->id);

        $this->assertJson("$testResource");
    }

    /**
     * Test getting a hidden field in a resource
     *
     * @return void
     */
    public function testGettingAHiddenField()
    {
        $passwordData = [
            'password' => 'testing'
        ];

        $this->testingResource = new CreateTestResource($passwordData);

        $testModel = app('TestModel');
        $testResource = $testModel->get($this->testingResource->user->id, ['password']);

        $this->assertEquals($this->testingResource->user->id, $testResource->id);
        $this->assertArraySubset($passwordData, Redis::hgetall($this->testingResource->testModelHashId));
        $this->assertNull($testResource->password);
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
