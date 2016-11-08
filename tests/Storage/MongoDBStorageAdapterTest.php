<?php

namespace Opensoft\Tests\Storage;

use Opensoft\Rollout\Storage\MongoDBStorageAdapter;

class MongoDBStorageAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $mongo;

    public function setUp()
    {
        $this->mongo = new mockMongo();
        $collection = $this->mockCollection();
        $collection->method('findOne')->will($this->returnValue(['name' => 'key', 'value' => true]));
        $collection->method('update')->will($this->returnValue(null));

        $this->mongo->setCollection($collection);
    }

    public function testGet()
    {

        $adapter = new MongoDBStorageAdapter($this->mongo);

        $result = $adapter->get('key');
        $this->assertSame(true, $result);
    }

    public function testGetNoValue()
    {

        $adapter = new MongoDBStorageAdapter($this->mongo);
        $this->mongo->coll->method('findOne')->will($this->returnValue(null));
        $result = $adapter->get('key');
        $this->assertSame(true, $result);
    }

    public function testSet()
    {

        $adapter = new MongoDBStorageAdapter($this->mongo);

        $adapter->set('key', 'value');

    }

    public function testRemove()
    {

        $adapter = new MongoDBStorageAdapter($this->mongo);

        $adapter->remove('key');

    }

    public function testGetCollectionName()
    {

        $adapter = new MongoDBStorageAdapter($this->mongo, 'feature_test');

        $result = $adapter->getCollectionName();
        $this->assertSame('feature_test', $result);

    }

    public function mockCollection()
    {
        return $this->createMock('MongoCollection', ['find', 'findOne', 'update', 'remove'], [], '', false);

    }
}

class mockMongo
{
    public $collection;
    public function __construct()
    {

    }

    public function setCollection($mongoCollection)
    {
        $this->collection = $mongoCollection;
    }

    public function __get($name)
    {
        return $this->collection;
    }
}
