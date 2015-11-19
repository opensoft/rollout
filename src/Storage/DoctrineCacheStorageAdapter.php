<?php
/**
 * 
 */

namespace Opensoft\Rollout\Storage;

use Doctrine\Common\Cache\Cache;

/**
 * Use any available doctrine/cache Cache implementation
 *
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class DoctrineCacheStorageAdapter implements StorageInterface
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Cache $cache
     */
    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param  string $key
     * @return mixed|null Null if the value is not found
     */
    public function get($key)
    {
        return $this->cache->fetch($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->cache->save($key, $value);
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        $this->cache->delete($key);
    }
} 
