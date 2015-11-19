<?php

namespace Opensoft\Tests\Storage;

use Opensoft\Rollout\Storage\RedisStorageAdapter;

class RedisStorageAdapterTest extends \PHPUnit_Framework_TestCase
{
    private $redis;

    public function setUp()
    {
        $this->redis = $this->getMockBuilder('\Opensoft\Tests\Storage\mockRedis')->getMock();
    }

    public function testGet()
    {
        $this->redis->expects($this->once())
            ->method('hget')
            ->with(RedisStorageAdapter::DEFAULT_GROUP, 'key')
            ->willReturn(json_encode('success'));

        $adapter = new RedisStorageAdapter($this->redis);

        $result = $adapter->get('key');
        $this->assertSame('success', $result);
    }

    public function testGetWithCustomGroup()
    {
        $this->redis->expects($this->once())
            ->method('hget')
            ->with('rollout_test', 'key')
            ->willReturn(json_encode('success'));

        $adapter = new RedisStorageAdapter($this->redis, 'rollout_test');

        $result = $adapter->get('key');
        $this->assertSame('success', $result);
    }

    public function testGetNotExistsFailure()
    {
        $this->redis->expects($this->once())
            ->method('hget')
            ->with(RedisStorageAdapter::DEFAULT_GROUP, 'key')
            ->willReturn('');

        $adapter = new RedisStorageAdapter($this->redis);

        $result = $adapter->get('key');
        $this->assertNull($result);
    }

    public function testGetJsonDecodeFailure()
    {
        $this->redis->expects($this->once())
            ->method('hget')
            ->with(RedisStorageAdapter::DEFAULT_GROUP, 'key')
            ->willReturn('not json');

        $adapter = new RedisStorageAdapter($this->redis);

        $result = $adapter->get('key');
        $this->assertNull($result);
    }

    public function testSet()
    {
        $this->redis->expects($this->once())
            ->method('hset')
            ->with(RedisStorageAdapter::DEFAULT_GROUP, 'key', json_encode('value'));

        $adapter = new RedisStorageAdapter($this->redis);

        $adapter->set('key', 'value');
    }

    public function testRemove()
    {
        $this->redis->expects($this->once())
            ->method('hdel')
            ->with(RedisStorageAdapter::DEFAULT_GROUP, 'key');

        $adapter = new RedisStorageAdapter($this->redis);

        $adapter->remove('key');
    }

    public function testSetWithCustomGroup()
    {
        $this->redis->expects($this->once())
            ->method('hset')
            ->with('rollout_test', 'key', json_encode('value'));

        $adapter = new RedisStorageAdapter($this->redis, 'rollout_test');

        $adapter->set('key', 'value');
    }
}

interface mockRedis
{
    public function hget($key, $field);
    public function hset($key, $field, $value);
    public function hdel($key, $field);
}
