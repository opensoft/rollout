<?php

namespace Opensoft\Rollout\Storage;

/**
 * Storage adapter using PDO
 *
 * @author Baldur Rensch <brensch@gmail.com>
 */
class PDOStorageAdapter implements StorageInterface
{
    const STMT_SELECT = 'SELECT settings FROM :table WHERE name = :key';
    const STMT_INSERT = 'INSERT INTO :table (name, settings) VALUES (:key, :value)';
    const STMT_UPDATE = 'UPDATE :table SET settings = :value WHERE name = :key';
    const STMT_DELETE = 'DELETE FROM :table WHERE name = :key';

    /**
     * @var \PDO
     */
    private $pdoConnection;

    /**
     * @var string
     */
    private $tableName;

    public function __construct(\PDO $pdoConnection, $tableName = 'rollout_feature')
    {
        $this->pdoConnection = $pdoConnection;
        $this->tableName = $tableName;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        $statement = $this->pdoConnection->prepare($this->getSQLStatement(self::STMT_SELECT));

        $statement->bindParam('key', $key);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);

        $statement->execute();

        $result = $statement->fetch();

        if (false === $result) {
            return null;
        }

        return $result['settings'];
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if (null === $this->get($key)) {
            $sql = self::STMT_INSERT;
        } else {
            $sql = self::STMT_UPDATE;
        }

        $statement = $this->pdoConnection->prepare($this->getSQLStatement($sql));

        $statement->bindParam('key', $key);
        $statement->bindParam('value', $value);

        $statement->execute();
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        $statement = $this->pdoConnection->prepare($this->getSQLStatement(self::STMT_DELETE));

        $statement->bindParam('key', $key);
        $statement->execute();
    }

    /**
     * @param string $sql
     *
     * @return string
     */
    private function getSQLStatement($sql)
    {
        return str_replace(':table', $this->tableName, $sql);
    }
}
