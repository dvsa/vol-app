<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Dvsa\Olcs\Api\Domain\Repository\DataGovUk;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\DataGovUk::class)]
class DataGovUkTest extends MockeryTestCase
{
    public $mockResult;
    /** @var  m\MockInterface */
    private $mockStmt;
    /** @var  m\MockInterface */
    private $mockConn;

    /** @var DataGovUk */
    private $sut;

    public function setUp(): void
    {
        $this->mockResult = m::mock(Result::class);

        $this->mockStmt = m::mock(Statement::class);
        $this->mockStmt->expects('executeQuery')->withNoArgs()->andReturn($this->mockResult);

        $this->mockConn = m::mock(Connection::class);
        $this->mockConn->shouldReceive('close')->withNoArgs();

        $this->sut = new DataGovUk($this->mockConn);
    }

    public function testFetchPsvOperatorList(): void
    {
        $this->mockConn
            ->shouldReceive('prepare')
            ->once()
            ->with(m::pattern('/data_gov_uk_psv_operator_list$/'))
            ->andReturn($this->mockStmt);

        static::assertEquals(
            $this->mockResult,
            $this->sut->fetchPsvOperatorList()
        );
    }

    public function testFetchOperatorLicences(): void
    {
        $areas = ['areaKey1', 'areaKey2', 'areaKey3'];

        $this->mockStmt
            ->shouldReceive('bindValue')
            ->times(count($areas))
            ->with(m::anyOf(1, 2, 3), m::pattern('/^areaKey/'), \PDO::PARAM_STR);

        $this->mockConn
            ->shouldReceive('prepare')
            ->once()
            ->with(m::pattern('/data_gov_uk_operator_licence_view (.*)IN \(\?, \?, \?\)$/'))
            ->andReturn($this->mockStmt);

        static::assertEquals(
            $this->mockResult,
            $this->sut->fetchOperatorLicences($areas)
        );
    }

    public function testBusRegisteredOnly(): void
    {
        $areas = ['areaKey1', 'areaKey2', 'areaKey3'];

        $this->mockStmt
            ->shouldReceive('bindValue')
            ->times(count($areas))
            ->with(m::anyOf(1, 2, 3), m::pattern('/^areaKey/'), \PDO::PARAM_STR);

        $this->mockConn
            ->shouldReceive('prepare')
            ->once()
            ->with(m::pattern('/data_gov_uk_bus_registered_only_view (.*)IN \(\?, \?, \?\)$/'))
            ->andReturn($this->mockStmt);

        static::assertEquals(
            $this->mockResult,
            $this->sut->fetchBusRegisteredOnly($areas)
        );
    }

    public function testBusVariation(): void
    {
        $areas = ['areaKey1'];

        $this->mockStmt
            ->shouldReceive('bindValue')
            ->times(count($areas))
            ->with(1, 'areaKey1', \PDO::PARAM_STR);

        /** @var Connection $mockConn */
        $this->mockConn
            ->shouldReceive('prepare')
            ->once()
            ->with(m::pattern('/data_gov_uk_bus_variation_view (.*)IN \(\?\)$/'))
            ->andReturn($this->mockStmt);

        static::assertEquals(
            $this->mockResult,
            $this->sut->fetchBusVariation($areas)
        );
    }
}
