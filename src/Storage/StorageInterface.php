<?php
/**
 * 
 */

namespace Opensoft\Rollout\Storage;

/**
 * @author Richard Fullmer <richard.fullmer@opensoftdev.com>
 */
interface StorageInterface
{
    /**
     * @param  string $key
     * @return mixed|null Null if the value is not found
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value);
} 
