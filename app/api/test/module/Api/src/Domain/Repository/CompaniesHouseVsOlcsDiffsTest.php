<?php

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseVsOlcsDiffs;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseVsOlcsDiffs
 */
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

    public function testFetchOfficerDiffs()
    {
        $this->mockConn
            ->shouldReceive('executeQuery')
            ->once()
            ->with(m::pattern('/^CALL sp_ch_vs_olcs_diff_/'))

            ->andReturn($this->mockStmt);

        static::assertSame($this->mockStmt, $this->sut->fetchOfficerDiffs());
    }

    public function testFetchAddressDiffs()
    {
        $this->mockConn
            ->shouldReceive('executeQuery')
            ->once()
            ->with(m::pattern('/^CALL sp_ch_vs_olcs_diff_/'))
            ->andReturn($this->mockStmt);

        static::assertSame($this->mockStmt, $this->sut->fetchAddressDiffs());
    }

    public function testFetchNameDiffs()
    {
        $this->mockConn
            ->shouldReceive('executeQuery')
            ->once()
            ->with(m::pattern('/^CALL sp_ch_vs_olcs_diff_/'))
            ->andReturn($this->mockStmt);

        static::assertSame($this->mockStmt, $this->sut->fetchNameDiffs());
    }

    public function testFetchWithNotActiveStatus()
    {
        $this->mockConn
            ->shouldReceive('executeQuery')
            ->once()
            ->with(m::pattern('/^CALL sp_ch_vs_olcs_diff/'))
            ->andReturn($this->mockStmt);

        static::assertSame($this->mockStmt, $this->sut->fetchWithNotActiveStatus());
    }
}
