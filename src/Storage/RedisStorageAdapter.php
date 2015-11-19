<?php

namespace Opensoft\Rollout\Storage;

/**
 * Storage adapter using Redis
 *
 * @author Woody Gilk <@shadowhand>
 */
class RedisStorageAdapter implements StorageInterface
{
    /**
     * @var string
     */
    const DEFAULT_GROUP = 'rollout_feature';

    /**
     * @var object
     */
    private $redis;

    /**
     * @var string
     */
    private $group = self::DEFAULT_GROUP;

    public function __construct($redis, $group = null)
    {
        $this->redis = $redis;

        if ($group) {
            $this->group = $group;
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $result = $this->redis->hget($this->group, $key);

        if (empty($result)) {
            return null;
        }

        $result = json_decode($result, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            return null;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->redis->hset($this->group, $key, json_encode($value));
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        $this->redis->hdel($this->group, $key);
    }
}
