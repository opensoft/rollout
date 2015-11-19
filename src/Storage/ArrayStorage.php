<?php
/**
 * 
 */

namespace Opensoft\Rollout\Storage;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
class ArrayStorage implements StorageInterface
{
    /**
     * @var array
     */
    private $storage = array();

    /**
     * @param string $key
     * @return mixed|null
     */
    public function get($key)
    {
        return isset($this->storage[$key]) ? $this->storage[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->storage[$key] = $value;
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        if (isset($this->storage[$key])) {
            unset($this->storage[$key]);
        }
    }
} 
