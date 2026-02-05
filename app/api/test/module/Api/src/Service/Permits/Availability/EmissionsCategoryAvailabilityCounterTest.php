<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Availability;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\TransactionIsolationLevel;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermit as IrhpPermitRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitRange as IrhpPermitRangeRepository;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as IrhpPermitStockRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Availability\EmissionsCategoryAvailabilityCounter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * EmissionsCategoryAvailabilityCounterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsCategoryAvailabilityCounterTest extends MockeryTestCase
{
    private $connection;

    private $irhpPermitRangeRepo;

    private $irhpPermitApplicationRepo;

    private $irhpPermitRepo;

    private $irhpPermitStockRepo;

    private $irhpCandidatePermitRepo;

    private $emissionsCategoryAvailabilityCounter;

    public function setUp(): void
    {
        $this->connection = m::mock(Connection::class);

        $this->irhpPermitRangeRepo = m::mock(IrhpPermitRangeRepository::class);

        $this->irhpPermitApplicationRepo = m::mock(IrhpPermitApplicationRepository::class);

        $this->irhpPermitRepo = m::mock(IrhpPermitRepository::class);

        $this->irhpPermitStockRepo = m::mock(IrhpPermitStockRepository::class);

        $this->irhpCandidatePermitRepo = m::mock(IrhpCandidatePermitRepository::class);

        $this->emissionsCategoryAvailabilityCounter = new EmissionsCategoryAvailabilityCounter(
            $this->connection,
            $this->irhpPermitRangeRepo,
            $this->irhpPermitApplicationRepo,
            $this->irhpPermitRepo,
            $this->irhpPermitStockRepo,
            $this->irhpCandidatePermitRepo
        );
    }

    public function testGetCountEmissionsCategoriesAllocationMode(): void
    {
        $irhpPermitStockId = 22;
        $emissionsCategoryId = RefData::EMISSIONS_CATEGORY_EURO5_REF;

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getAllocationMode')
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_EMISSIONS_CATEGORIES);

        $this->connection->shouldReceive('getTransactionIsolation')
            ->withNoArgs()
            ->andReturn(TransactionIsolationLevel::REPEATABLE_READ);

        $this->connection->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitRangeRepo->shouldReceive('getCombinedRangeSize')
            ->with($irhpPermitStockId, $emissionsCategoryId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(40);

        $this->irhpPermitStockRepo->shouldReceive('fetchById')
            ->with($irhpPermitStockId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($irhpPermitStock);

        $this->irhpPermitApplicationRepo->shouldReceive('getRequiredPermitCountWhereApplicationAwaitingPayment')
            ->with($irhpPermitStockId, $emissionsCategoryId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(8);

        $this->irhpPermitRepo->shouldReceive('getPermitCount')
            ->with($irhpPermitStockId, $emissionsCategoryId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(14);

        $this->connection->shouldReceive('commit')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->assertEquals(
            18,
            $this->emissionsCategoryAvailabilityCounter->getCount($irhpPermitStockId, $emissionsCategoryId)
        );
    }

    public function testGetCountCandidatePermitsAllocationMode(): void
    {
        $irhpPermitStockId = 22;
        $emissionsCategoryId = RefData::EMISSIONS_CATEGORY_EURO5_REF;

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getAllocationMode')
            ->andReturn(IrhpPermitStock::ALLOCATION_MODE_CANDIDATE_PERMITS);

        $this->connection->shouldReceive('getTransactionIsolation')
            ->withNoArgs()
            ->andReturn(TransactionIsolationLevel::REPEATABLE_READ);

        $this->connection->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitRangeRepo->shouldReceive('getCombinedRangeSize')
            ->with($irhpPermitStockId, $emissionsCategoryId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(40);

        $this->irhpPermitStockRepo->shouldReceive('fetchById')
            ->with($irhpPermitStockId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($irhpPermitStock);

        $this->irhpCandidatePermitRepo->shouldReceive('fetchCountInStockWhereApplicationAwaitingFee')
            ->with($irhpPermitStockId, $emissionsCategoryId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(10);

        $this->irhpPermitRepo->shouldReceive('getPermitCount')
            ->with($irhpPermitStockId, $emissionsCategoryId)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(14);

        $this->connection->shouldReceive('commit')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->assertEquals(
            16,
            $this->emissionsCategoryAvailabilityCounter->getCount($irhpPermitStockId, $emissionsCategoryId)
        );
    }

    public function testReturnZeroOnNullCombinedRangeSize(): void
    {
        $irhpPermitStockId = 47;
        $emissionsCategoryId = RefData::EMISSIONS_CATEGORY_EURO6_REF;

        $this->connection->shouldReceive('getTransactionIsolation')
            ->withNoArgs()
            ->andReturn(TransactionIsolationLevel::REPEATABLE_READ);

        $this->connection->shouldReceive('beginTransaction')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->irhpPermitRangeRepo->shouldReceive('getCombinedRangeSize')
            ->with($irhpPermitStockId, $emissionsCategoryId)
            ->andReturn(null);

        $this->connection->shouldReceive('commit')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->assertEquals(
            0,
            $this->emissionsCategoryAvailabilityCounter->getCount($irhpPermitStockId, $emissionsCategoryId)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestExceptionOnUnexpectedIsolationLevel')]
    public function testExceptionOnUnexpectedIsolationLevel(mixed $isolationLevel): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(EmissionsCategoryAvailabilityCounter::ERR_BAD_ISOLATION_LEVEL);

        $this->connection->shouldReceive('getTransactionIsolation')
            ->withNoArgs()
            ->andReturn($isolationLevel);

        $this->emissionsCategoryAvailabilityCounter->getCount(47, RefData::EMISSIONS_CATEGORY_EURO6_REF);
    }

    public static function dpTestExceptionOnUnexpectedIsolationLevel(): array
    {
        return [
            [TransactionIsolationLevel::READ_UNCOMMITTED],
            [TransactionIsolationLevel::READ_COMMITTED],
            [TransactionIsolationLevel::SERIALIZABLE],
        ];
    }
}
