<?php

namespace Opensoft\Tests\Storage;

use Opensoft\Rollout\Storage\PDOStorageAdapter;

class PDOStorageAdapterTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $statement = $this->mockPDOStatement();

        $statement->expects($this->once())
            ->method('bindParam')
            ->with('key', 'test');

        $statement->expects($this->once())
            ->method('execute');

        $statement->expects($this->once())
            ->method('fetch')
            ->willReturn(array('settings' => 'success'));

        $pdo = $this->mockPDO($this->prepareSQL(PDOStorageAdapter::STMT_SELECT), $statement);

        $adapter = new PDOStorageAdapter($pdo);

        $result = $adapter->get('test');
        $this->assertEquals('success', $result);
    }

    public function testRemove()
    {
        $statement = $this->mockPDOStatement();

        $statement->expects($this->once())
            ->method('bindParam')
            ->with('key', 'test');

        $statement->expects($this->once())
            ->method('execute');

        $pdo = $this->mockPDO($this->prepareSQL(PDOStorageAdapter::STMT_DELETE), $statement);

        $adapter = new PDOStorageAdapter($pdo);
        $adapter->remove('test');
    }

    public function testTableName()
    {
        $statement = $this->mockPDOStatement();

        $pdo = $this->mockPDO($this->prepareSQL(PDOStorageAdapter::STMT_SELECT, 'rollout_feature2'), $statement);

        $adapter = new PDOStorageAdapter($pdo, 'rollout_feature2');

        $adapter->get('test');
    }

    public function testSetInsert()
    {
        $getStatement = $this->mockPDOStatement();

        $setStatement = $this->mockPDOStatement();
        $setStatement->expects($this->at(0))
            ->method('bindParam')
            ->with('key', 'test');

        $setStatement->expects($this->at(1))
            ->method('bindParam')
            ->with('value', 'value');

        $pdo = $this->mockPDO($this->prepareSQL(PDOStorageAdapter::STMT_SELECT), $getStatement);

        $pdo->expects($this->at(1))
            ->method('prepare')
            ->with($this->prepareSQL(PDOStorageAdapter::STMT_INSERT))
            ->willReturn($setStatement);

        $adapter = new PDOStorageAdapter($pdo, 'rollout_feature');

        $adapter->set('test', 'value');
    }

    public function testSetUpdate()
    {
        $getStatement = $this->mockPDOStatement();
        $getStatement->expects($this->once())
            ->method('fetch')
            ->willReturn(array('settings' => 'success'));

        $setStatement = $this->mockPDOStatement();
        $setStatement->expects($this->at(0))
            ->method('bindParam')
            ->with('key', 'test');

        $setStatement->expects($this->at(1))
            ->method('bindParam')
            ->with('value', 'value');

        $pdo = $this->mockPDO($this->prepareSQL(PDOStorageAdapter::STMT_SELECT), $getStatement);

        $this->mockPDOPrepare($pdo, 1, $this->prepareSQL(PDOStorageAdapter::STMT_UPDATE), $setStatement);

        $adapter = new PDOStorageAdapter($pdo, 'rollout_feature');

        $adapter->set('test', 'value');
    }

    private function prepareSQL($sql, $table = 'rollout_feature')
    {
        return str_replace(':table', $table, $sql);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPDOStatement()
    {
        $statement = $this->getMockBuilder('\PDOStatement')
            ->getMock();

        return $statement;
    }

    /**
     * @param $query
     * @param $statement
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockPDO($query, $statement)
    {
        $pdo = $this->getMockBuilder('\Opensoft\Tests\Storage\mockPDO')
            ->getMock();

        $this->mockPDOPrepare($pdo, 0, $query, $statement);

        return $pdo;
    }

    private function mockPDOPrepare(\PHPUnit_Framework_MockObject_MockObject $pdo, $at, $query, $statement)
    {
        $pdo->expects($this->at($at))
            ->method('prepare')
            ->with($query)
            ->willReturn($statement);
    }
}

class mockPDO extends \PDO
{
    public function __construct()
    {
    }
}
