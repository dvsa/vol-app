<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as Repo;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as Entity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;

final class IrhpPermitStockTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' ips';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('readyToPrintProvider')]
    public function testFetchReadyToPrint(?int $countryId, string $expectedExtra): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchReadyToPrint(IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL, $countryId),
        );

        $this->assertSame(
            'SELECT DISTINCT ips' . self::FROM
            . ' INNER JOIN ips.irhpPermitRanges ipr INNER JOIN ipr.irhpPermits ip'
            . ' WHERE ip.status IN(:statuses) AND ips.irhpPermitType = :irhpPermitTypeId'
            . $expectedExtra
            . ' ORDER BY ips.validFrom DESC',
            $qb->getDQL(),
        );
        $this->assertSame(IrhpPermitEntity::$readyToPrintStatuses, $qb->getParameter('statuses')->getValue());
    }

    public static function readyToPrintProvider(): \Iterator
    {
        yield 'any country' => [null, ''];
        yield 'one country' => [1, ' AND ips.country = :countryId'];
    }

    public function testFetchAll(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchAll());

        $this->assertSame('SELECT ips' . self::FROM, $qb->getDQL());
    }

    /**
     * Morocco additionally orders by permit category, which requires a join the other countries
     * do not take.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('openBilateralProvider')]
    public function testFetchOpenBilateralStocksByCountry(string $country, string $expectedExtra): void
    {
        $now = new DateTime('2019-01-01');

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('setHint')->with(Query::HINT_INCLUDE_META_COLUMNS, true)->andReturnSelf();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchOpenBilateralStocksByCountry($country, $now));

        $this->assertSame(
            'SELECT ips' . self::FROM
            . ' INNER JOIN ips.irhpPermitType ipt INNER JOIN ips.irhpPermitWindows ipw'
            . ' INNER JOIN ips.country c'
            . $expectedExtra
            . ' WHERE ips.country = :country AND ipw.startDate <= :now AND ipw.endDate > :now'
            . ' AND ipt.id = :type'
            . ($expectedExtra === '' ? '' : ' ORDER BY r.displayOrder ASC, ips.validTo ASC'),
            $qb->getDQL(),
        );
        $this->assertSame($country, $qb->getParameter('country')->getValue());
        $this->assertSame($now, $qb->getParameter('now')->getValue());
        $this->assertSame(
            IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL,
            $qb->getParameter('type')->getValue(),
        );
    }

    public static function openBilateralProvider(): \Iterator
    {
        yield 'not morocco' => ['FR', ''];
        yield 'morocco' => [Country::ID_MOROCCO, ' INNER JOIN ips.permitCategory r'];
    }
}
