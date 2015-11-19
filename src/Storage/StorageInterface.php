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
     * @return string|null Null if the value is not found
     */
    public function get($key);

    /**
     * @param string $key
     * @param string $value
     * @return void
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @return void
     */
    public function remove($key);
} 
