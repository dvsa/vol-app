<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\DataRetention;

use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\Query\DataRetention\Postcheck as Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention\Postcheck;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class PostcheckTest extends QueryHandlerTestCase
{
    private $mockedConnection;

    public function setUp(): void
    {
        $this->sut = new Postcheck();
        $this->mockedConnection = m::mock(\PDO::class);

        $this->mockedSmServices['doctrine.entitymanager.orm_default'] = m::mock(EntityManager::class);
        $this->mockedSmServices['doctrine.entitymanager.orm_default']
            ->expects('getConnection->getNativeConnection')
            ->withNoArgs()
            ->andReturn($this->mockedConnection);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $expected = [
            [
                'type' => 'rowcount',
                'tableName' => 'queue',
                'data1' => '28',
                'data2' => '27'
            ],
            [
                'type' => 'rowcount',
                'tableName' => 'answer',
                'data1' => '30',
                'data2' => '29'
            ]
        ];

        $mockStatement = m::mock(\PDOStatement::class);
        $mockStatement
            ->expects('execute')
            ->with()
            ->andReturnTrue();
        $mockStatement->expects('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->andReturn($expected);
        $mockStatement->expects('closeCursor')->withNoArgs();

        $this->mockedConnection->expects('prepare')->with("CALL sp_dr_postcheck();")->andReturn($mockStatement);
        $query = Query::create([]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals($expected, $result);
    }
}
