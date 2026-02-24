<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Mockery as m;

/**
 * IRHP Permit Stock test
 *
 * @author Jason de Jonge <jason.de-jonge@capgemini.co.uk>
 */
class IrhpPermitStockTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpPermitStock::class);
    }

    public function testFetchReadyToPrint(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchReadyToPrint(IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT));

        $expectedQuery = 'BLAH '
            . 'SELECT ips DISTINCT '
            . 'INNER JOIN ips.irhpPermitRanges ipr '
            . 'INNER JOIN ipr.irhpPermits ip '
            . 'AND ip.status IN [[['
                . '"' . IrhpPermitEntity::STATUS_PENDING . '",'
                . '"' . IrhpPermitEntity::STATUS_AWAITING_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_ERROR . '"'
            . ']]] '
            . 'AND ips.irhpPermitType = [[' . IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT . ']] '
            . 'ORDER BY ips.validFrom DESC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchReadyToPrintBilateral(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(
            ['RESULTS'],
            $this->sut->fetchReadyToPrint(IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL, 'DE')
        );

        $expectedQuery = 'BLAH '
            . 'SELECT ips DISTINCT '
            . 'INNER JOIN ips.irhpPermitRanges ipr '
            . 'INNER JOIN ipr.irhpPermits ip '
            . 'AND ip.status IN [[['
                . '"' . IrhpPermitEntity::STATUS_PENDING . '",'
                . '"' . IrhpPermitEntity::STATUS_AWAITING_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_ERROR . '"'
            . ']]] '
            . 'AND ips.irhpPermitType = [[' . IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL . ']] '
            . 'AND ips.country = [[DE]] '
            . 'ORDER BY ips.validFrom DESC';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchAll(): void
    {
        $irhpPermitStocks = [
            m::mock(IrhpPermitStockEntity::class),
            m::mock(IrhpPermitStockEntity::class),
        ];

        $queryBuilder = m::mock(QueryBuilder::class);
        $queryBuilder->shouldReceive('getQuery->getResult')
            ->andReturn($irhpPermitStocks);

        $this->mockCreateQueryBuilder($queryBuilder);

        $this->assertEquals(
            $irhpPermitStocks,
            $this->sut->fetchAll()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpFetchOpenBilateralStocksByCountryNotMorocco')]
    public function testFetchOpenBilateralStocksByCountryNotMorocco(mixed $countryId): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $query = m::mock(AbstractQuery::class);
        $query->shouldReceive('setHint')
            ->with(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->once()
            ->ordered()
            ->andReturnSelf()
            ->shouldReceive('getResult')
            ->with(Query::HYDRATE_ARRAY)
            ->once()
            ->ordered()
            ->andReturn(['RESULTS']);

        $qb->shouldReceive('getQuery')
            ->withNoArgs()
            ->andReturn($query);

        $now = new DateTime();

        $this->assertEquals(
            ['RESULTS'],
            $this->sut->fetchOpenBilateralStocksByCountry($countryId, $now)
        );

        $iso8601String = $now->format(DateTime::W3C);

        $expectedQuery = 'BLAH ' .
        'SELECT ips ' .
        'INNER JOIN ips.irhpPermitType ipt ' .
        'INNER JOIN ips.irhpPermitWindows ipw ' .
        'INNER JOIN ips.country c ' .
        "AND ips.country = [[$countryId]] " .
        "AND ipw.startDate <= [[$iso8601String]] " .
        "AND ipw.endDate > [[$iso8601String]] AND ipt.id = [[4]]";

        $this->assertEquals($expectedQuery, $this->query);
    }

    public static function dpFetchOpenBilateralStocksByCountryNotMorocco(): array
    {
        return [
            [Country::ID_NORWAY],
            [Country::ID_BELARUS],
            [Country::ID_GEORGIA],
        ];
    }

    public function testFetchOpenBilateralStocksByCountryMorocco(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $query = m::mock(AbstractQuery::class);
        $query->shouldReceive('setHint')
            ->with(Query::HINT_INCLUDE_META_COLUMNS, true)
            ->once()
            ->ordered()
            ->andReturnSelf()
            ->shouldReceive('getResult')
            ->with(Query::HYDRATE_ARRAY)
            ->once()
            ->ordered()
            ->andReturn(['RESULTS']);

        $qb->shouldReceive('getQuery')
            ->withNoArgs()
            ->andReturn($query);

        $now = new DateTime();

        $this->assertEquals(
            ['RESULTS'],
            $this->sut->fetchOpenBilateralStocksByCountry(Country::ID_MOROCCO, $now)
        );

        $iso8601String = $now->format(DateTime::W3C);

        $expectedQuery = 'BLAH ' .
        'SELECT ips ' .
        'INNER JOIN ips.irhpPermitType ipt ' .
        'INNER JOIN ips.irhpPermitWindows ipw ' .
        'INNER JOIN ips.country c ' .
        'AND ips.country = [[MA]] ' .
        "AND ipw.startDate <= [[$iso8601String]] " .
        "AND ipw.endDate > [[$iso8601String]] AND ipt.id = [[4]]" .
        " INNER JOIN ips.permitCategory r ORDER BY r.displayOrder ASC ORDER BY ips.validTo ASC";

        $this->assertEquals($expectedQuery, $this->query);
    }
}
