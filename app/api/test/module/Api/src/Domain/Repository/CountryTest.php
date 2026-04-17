<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Country;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as CountryEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Mockery as m;

/**
 * Country test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CountryTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Country::class);
    }

    public function testFetchIdsAndDescriptions(): void
    {
        $idsAndDescriptions = [
            [
                'countryId' => 'AU',
                'description' => 'Austria'
            ],
            [
                'countryId' => 'RU',
                'description' => 'Russia'
            ],
        ];

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('c.id as countryId, c.countryDesc as description')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(CountryEntity::class, 'c')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getScalarResult')
            ->once()
            ->andReturn($idsAndDescriptions);

        $this->assertEquals(
            $idsAndDescriptions,
            $this->sut->fetchIdsAndDescriptions()
        );
    }

    public function testFetchAvailableCountriesForIrhpApplication(): void
    {
        $now = new DateTime('2018-10-25 13:21:10');

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
            $this->sut->fetchAvailableCountriesForIrhpApplication(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, $now)
        );

        $expectedQuery = 'BLAH '
            . 'SELECT m DISTINCT '
            . 'INNER JOIN m.irhpPermitStocks ips '
            . 'INNER JOIN ips.irhpPermitType ipt '
            . 'INNER JOIN ips.irhpPermitWindows ipw '
            . 'AND ipt.id = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL . ']] '
            . 'AND ipw.startDate <= [[2018-10-25T13:21:10+00:00]] '
            . 'AND ipw.endDate > [[2018-10-25T13:21:10+00:00]] '
            . 'ORDER BY m.countryDesc ASC';

        $this->assertEquals($expectedQuery, $this->query);
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
        $this->assertEquals(['RESULTS'], $this->sut->fetchReadyToPrint(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL));

        $expectedQuery = 'BLAH '
            . 'SELECT m DISTINCT '
            . 'INNER JOIN m.irhpPermitStocks ips '
            . 'INNER JOIN ips.irhpPermitRanges ipr '
            . 'INNER JOIN ipr.irhpPermits ip '
            . 'AND ip.status IN [[['
                . '"' . IrhpPermitEntity::STATUS_PENDING . '",'
                . '"' . IrhpPermitEntity::STATUS_AWAITING_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_PRINTING . '",'
                . '"' . IrhpPermitEntity::STATUS_ERROR . '"'
            . ']]] '
            . 'AND ips.irhpPermitType = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL . ']] '
            . 'ORDER BY m.countryDesc ASC';

        $this->assertEquals($expectedQuery, $this->query);
    }
}
