<?php

namespace Opensoft\Rollout\Storage;

/**
 * Storage adapter using MongoDB
 *
 * @author James Hrisho <@securingsincity>
 */
class MongoDBStorageAdapter implements StorageInterface
{

    /**
     * @var object
     */
    private $mongo;

    /**
     * @var string
     */
    private $collection;

    public function __construct($mongo, $collection = "rollout_feature")
    {
        $this->mongo = $mongo;
        $this->collection = $collection;
    }
    public function getCollectionName()
    {
        return $this->collection;
    }
    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $collection = $this->getCollectionName();
        $result = $this->mongo->$collection->findOne(['name' => $key]);

        if (!$result) {
            return null;
        }

        return $result['value'];
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $collection = $this->getCollectionName();
        $this->mongo->$collection->update(['name' => $key], ['$set' => ['value' => $value]], ['upsert' => true]);
    }

    /**
     * @inheritdoc
     */
    public function remove($key)
    {
        $collection = $this->getCollectionName();
        $this->mongo->$collection->remove(['name' => $key]);
    }

}
