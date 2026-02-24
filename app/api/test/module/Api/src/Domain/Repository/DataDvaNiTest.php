<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\DBAL\Statement;
use Dvsa\Olcs\Api\Domain\Repository\DataDvaNi;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\DataDvaNi::class)]
class DataDvaNiTest extends MockeryTestCase
{
    public $mockResult;
    /** @var  m\MockInterface */
    private $mockStmt;
    /** @var  m\MockInterface */
    private $mockConn;

    /** @var DataDvaNi */
    private $sut;

    public function setUp(): void
    {
        $this->mockResult = m::mock(Result::class);

        $this->mockStmt = m::mock(Statement::class);
        $this->mockStmt->expects('executeQuery')->andReturn($this->mockResult);

        $this->mockConn = m::mock(Connection::class);
        $this->mockConn->shouldReceive('close')->withNoArgs();

        $this->sut = new DataDvaNi($this->mockConn);
    }

    public function testFetchNiOperatorLicences(): void
    {
        $this->mockConn
            ->shouldReceive('prepare')
            ->once()
            ->with('SELECT * FROM data_dva_ni_operator_licence_view')
            ->andReturn($this->mockStmt);

        static::assertEquals(
            $this->mockResult,
            $this->sut->fetchNiOperatorLicences()
        );
    }
}
