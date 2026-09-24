<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Country;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;

final class CountryTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Country::class);
    }

    public function testFetchIdsAndDescriptions(): void
    {
        $idsAndDescriptions = [
            ['countryId' => 'AU', 'description' => 'Austria'],
            ['countryId' => 'RU', 'description' => 'Russia'],
        ];

        $qb = $this->newRealQb();
        $qb->stubbedQuery()->expects('getScalarResult')->andReturn($idsAndDescriptions);
        $this->em->expects('createQueryBuilder')->withNoArgs()->andReturn($qb);

        $this->assertSame($idsAndDescriptions, $this->sut->fetchIdsAndDescriptions());

        $this->assertSame(
            'SELECT c.id as countryId, c.countryDesc as description FROM ' . Entity::class . ' c',
            $qb->getDQL(),
        );
    }

    public function testFetchAvailableCountriesForIrhpApplication(): void
    {
        $now = new DateTime('2018-10-25 13:21:10');

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchAvailableCountriesForIrhpApplication(
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                $now,
            ),
        );

        $this->assertSame(
            'SELECT DISTINCT m FROM ' . Entity::class . ' m'
            . ' INNER JOIN m.irhpPermitStocks ips INNER JOIN ips.irhpPermitType ipt'
            . ' INNER JOIN ips.irhpPermitWindows ipw'
            . ' WHERE ipt.id = :type AND ipw.startDate <= :now AND ipw.endDate > :now'
            . ' ORDER BY m.countryDesc ASC',
            $qb->getDQL(),
        );
        $this->assertSame($now, $qb->getParameter('now')->getValue());
        $this->assertSame(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, $qb->getParameter('type')->getValue());
    }

    public function testFetchReadyToPrint(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchReadyToPrint(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL),
        );

        $this->assertSame(
            'SELECT DISTINCT m FROM ' . Entity::class . ' m'
            . ' INNER JOIN m.irhpPermitStocks ips INNER JOIN ips.irhpPermitRanges ipr'
            . ' INNER JOIN ipr.irhpPermits ip'
            . ' WHERE ip.status IN(:statuses) AND ips.irhpPermitType = :irhpPermitTypeId'
            . ' ORDER BY m.countryDesc ASC',
            $qb->getDQL(),
        );
        $this->assertSame(IrhpPermitEntity::$readyToPrintStatuses, $qb->getParameter('statuses')->getValue());
    }
}
