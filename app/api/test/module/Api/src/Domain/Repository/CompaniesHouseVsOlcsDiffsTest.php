<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseVsOlcsDiffs;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseVsOlcsDiffs::class)]
class CompaniesHouseVsOlcsDiffsTest extends MockeryTestCase
{
    /** @var  m\MockInterface */
    private $mockStmt;
    /** @var  m\MockInterface */
    private $mockConn;

    /** @var CompaniesHouseVsOlcsDiffs */
    private $sut;

    public function setUp(): void
    {
        $this->mockStmt = m::mock(Result::class);

        $this->mockConn = m::mock(Connection::class)
            ->shouldReceive('close')->atMost()
            ->getMock();

        $this->sut = new CompaniesHouseVsOlcsDiffs($this->mockConn);
    }

    public function testFetchOfficerDiffs(): void
    {
        $this->mockConn
            ->shouldReceive('executeQuery')
            ->once()
            ->with(m::pattern('/^CALL sp_ch_vs_olcs_diff_/'))

            ->andReturn($this->mockStmt);

        static::assertSame($this->mockStmt, $this->sut->fetchOfficerDiffs());
    }

    public function testFetchAddressDiffs(): void
    {
        $this->mockConn
            ->shouldReceive('executeQuery')
            ->once()
            ->with(m::pattern('/^CALL sp_ch_vs_olcs_diff_/'))
            ->andReturn($this->mockStmt);

        static::assertSame($this->mockStmt, $this->sut->fetchAddressDiffs());
    }

    public function testFetchNameDiffs(): void
    {
        $this->mockConn
            ->shouldReceive('executeQuery')
            ->once()
            ->with(m::pattern('/^CALL sp_ch_vs_olcs_diff_/'))
            ->andReturn($this->mockStmt);

        static::assertSame($this->mockStmt, $this->sut->fetchNameDiffs());
    }

    public function testFetchWithNotActiveStatus(): void
    {
        $this->mockConn
            ->shouldReceive('executeQuery')
            ->once()
            ->with(m::pattern('/^CALL sp_ch_vs_olcs_diff/'))
            ->andReturn($this->mockStmt);

        static::assertSame($this->mockStmt, $this->sut->fetchWithNotActiveStatus());
    }
}
