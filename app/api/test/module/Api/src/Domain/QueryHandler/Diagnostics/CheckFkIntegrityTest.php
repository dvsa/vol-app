<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Diagnostics;

use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\Query\Diagnostics\CheckFkIntegrity as CheckFkIntegrityCmd;
use Dvsa\Olcs\Api\Domain\Query\Diagnostics\GenerateCheckFkIntegritySql;
use Dvsa\Olcs\Api\Domain\QueryHandler\Diagnostics\CheckFkIntegrity;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Hamcrest\Core\IsInstanceOf;
use Mockery as m;
use PDO;
use PDOStatement;

class CheckFkIntegrityTest extends QueryHandlerTestCase
{
    /** @var m\MockInterface|PDO */
    private $mockPdo;

    public function setUp(): void
    {
        $mockDoctrineEntityManager = m::mock(EntityManager::class);
        $this->mockedSmServices['doctrine.entitymanager.orm_default'] = $mockDoctrineEntityManager;

        $this->mockPdo = m::mock(PDO::class);
        $mockDoctrineEntityManager
            ->shouldReceive('getConnection->getNativeConnection')
            ->andReturn($this->mockPdo);

        $this->sut = new CheckFkIntegrity();
        parent::setUp();
    }

    public function testQuery(): void
    {
        $this->queryHandler
            ->shouldReceive('handleQuery')
            ->with(new IsInstanceOf(GenerateCheckFkIntegritySql::class))
            ->andReturn(
                [
                    'queries' => [
                        'DUMMY-CREATE-QUERY',
                        'DUMMY-POPULATE-QUERY',
                    ]
                ]
            );

        $this->mockPdo
            ->shouldReceive('exec')
            ->with('DUMMY-CREATE-QUERY')
            ->once()
            ->globally()
            ->ordered();

        $this->mockPdo
            ->shouldReceive('exec')
            ->with('DUMMY-POPULATE-QUERY')
            ->once()
            ->globally()
            ->ordered();

        $mockStatement = m::mock(PDOStatement::class);
        $this->mockPdo
            ->shouldReceive('prepare')
            ->with("SELECT * FROM fk_integrity_violations_tmp WHERE violations > 0")
            ->andReturn(
                $mockStatement
            );

        $mockStatement
            ->shouldReceive('execute')
            ->once()
            ->globally()
            ->ordered();

        $mockStatement
            ->shouldReceive('fetchAll')
            ->once()
            ->with(PDO::FETCH_ASSOC)
            ->andReturn(
                [
                    ['fk_description' => 'DUMMY-DESC-1', 'violations' => '9'],
                    ['fk_description' => 'DUMMY-DESC-2', 'violations' => '8'],
                ]
            )
            ->globally()
            ->ordered();

        $this->assertSame(
            [
                'fk-constraint-violation-counts' => [
                    'DUMMY-DESC-1' => '9',
                    'DUMMY-DESC-2' => '8',
                ]
            ],
            $this->sut->handleQuery(
                CheckFkIntegrityCmd::create([])
            )
        );
    }
}
