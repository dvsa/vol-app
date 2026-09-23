<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as Repo;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as Entity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Mockery as m;

final class ContinuationDetailTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    /** withRefdata() joins status and signatureType. */
    private const string REFDATA_JOINS = ' LEFT JOIN m.status w0 LEFT JOIN m.signatureType w1';

    private const array ACTIVE_LICENCE_STATUSES = [
        LicenceEntity::LICENCE_STATUS_VALID,
        LicenceEntity::LICENCE_STATUS_CURTAILED,
        LicenceEntity::LICENCE_STATUS_SUSPENDED,
    ];

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * The date window is a six-way alternation covering four years either side of today, and it
     * is written as a raw DQL string rather than through the expression builder.
     */
    public function testFetchForLicence(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForLicence(7));

        $this->assertSame(
            'SELECT m, w0, w1, l, c' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.licence l LEFT JOIN m.continuation c'
            . ' WHERE m.licence = :licence AND l.status IN(:licenceStatuses)'
            . ' AND ((c.month >= :month AND c.year = :year)'
            . ' OR (c.year > :year AND c.year < :futureYear)'
            . ' OR (c.month <= :futureMonth AND c.year = :futureYear)'
            . ' OR (c.month <= :month AND c.year = :year)'
            . ' OR (c.year > :pastYear AND c.year < :year)'
            . ' OR (c.month >= :pastMonth AND c.year = :pastYear))'
            . ' AND m.status IN (:continuationDetailStatuses)',
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('licence')->getValue());
        $this->assertSame(self::ACTIVE_LICENCE_STATUSES, $qb->getParameter('licenceStatuses')->getValue());
        $this->assertSame(
            [Entity::STATUS_PRINTED, Entity::STATUS_ACCEPTABLE, Entity::STATUS_UNACCEPTABLE],
            $qb->getParameter('continuationDetailStatuses')->getValue(),
        );
    }

    /**
     * Ongoing means either acceptable, or digital and not yet complete.
     */
    public function testFetchOngoingForLicence(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchOngoingForLicence(7));

        $this->assertSame(
            'SELECT m, w0, w1, c' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.continuation c'
            . ' WHERE m.licence = :licence'
            . ' AND (m.status = :status OR (m.status <> :notStatus AND m.isDigital = 1))',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::STATUS_ACCEPTABLE, $qb->getParameter('status')->getValue());
        $this->assertSame(Entity::STATUS_COMPLETE, $qb->getParameter('notStatus')->getValue());
    }

    /**
     * Reminders go to paper continuations that have not been received. The select is a set of
     * partials, so only the columns the reminder needs are fetched.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('checklistProvider')]
    public function testFetchChecklistReminders(array $ids, ?int $month, ?int $year, string $expectedExtra): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([]);

        $this->assertCount(0, $this->sut->fetchChecklistReminders(['B'], $month, $year, $ids));

        $this->assertSame(
            'SELECT m, partial l.{id, licNo}, partial lgp.{id},'
            . ' partial lo.{id, name, allowEmail}, partial ls.{id, description},'
            . ' partial lf.{id, feeType, feeStatus}, partial lfft.{id},'
            . ' partial lfftft.{id}, partial lffs.{id}'
            . self::FROM . self::REFDATA_JOINS
            . ' INNER JOIN m.continuation c INNER JOIN m.licence l'
            . ' LEFT JOIN l.status ls LEFT JOIN l.goodsOrPsv lgp LEFT JOIN l.organisation lo'
            . ' LEFT JOIN l.fees lf LEFT JOIN lf.feeType lfft LEFT JOIN lfft.feeType lfftft'
            . ' LEFT JOIN lf.feeStatus lffs'
            . ' WHERE l.status IN(:licenceStatuses) AND m.received = 0 AND m.isDigital = 0'
            . $expectedExtra
            . ' AND l.trafficArea IN(:trafficAreas) AND m.status <> :status',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::STATUS_PREPARED, $qb->getParameter('status')->getValue());
    }

    public static function checklistProvider(): \Iterator
    {
        yield 'no narrowing' => [[], null, null, ''];
        yield 'by ids' => [[1, 2], null, null, ' AND m.id IN(:byIds)'];
        yield 'by month and year' => [
            [],
            5,
            2019,
            ' AND c.month = :month AND c.year = :year',
        ];
    }

    /**
     * Licences that already have an outstanding continuation fee are dropped after the query,
     * in PHP, rather than being excluded in DQL.
     */
    public function testFetchChecklistRemindersFiltersOutLicencesWithAnOutstandingFee(): void
    {
        $withFee = $this->continuationDetailWithFee(
            FeeTypeEntity::FEE_TYPE_CONT,
            FeeEntity::STATUS_OUTSTANDING,
        );
        $withoutFee = $this->continuationDetailWithFee('OTHER', FeeEntity::STATUS_OUTSTANDING);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->andReturn([$withFee, $withoutFee]);

        $result = $this->sut->fetchChecklistReminders(['B'], null, null);

        $this->assertCount(1, $result);
        $this->assertSame($withoutFee, $result->first());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('detailsProvider')]
    public function testFetchDetails(string $method, int $expectedAllowEmail): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchDetails(1, ['lsts_valid'], 'OB123', $method, Entity::STATUS_PRINTED),
        );

        // m.status is joined twice — w0 by withRefdata and s explicitly.
        $this->assertSame(
            'SELECT m, w0, w1, c, s, l, ls, lo, lt, lg' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.continuation c LEFT JOIN m.status s LEFT JOIN m.licence l'
            . ' LEFT JOIN l.status ls LEFT JOIN l.organisation lo'
            . ' LEFT JOIN l.licenceType lt LEFT JOIN l.goodsOrPsv lg'
            . ' WHERE c.id = :continuationId AND l.status IN(:licenceStatuses)'
            . ' AND l.licNo = :licNo AND lo.allowEmail = ' . $expectedAllowEmail
            . ' AND m.status = :status'
            . ' ORDER BY l.licNo ASC',
            $qb->getDQL(),
        );
    }

    public static function detailsProvider(): \Iterator
    {
        yield 'email' => [Entity::METHOD_EMAIL, 1];
        yield 'post' => [Entity::METHOD_POST, 0];
    }

    public function testFetchWithLicence(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchWithLicence(1));

        $this->assertSame(
            'SELECT m, w0, w1, s, l, lt, lg' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.status s LEFT JOIN m.licence l'
            . ' LEFT JOIN l.licenceType lt LEFT JOIN l.goodsOrPsv lg'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testFetchLicenceIdsForContinuationAndLicences(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')
            ->with(Query::HYDRATE_ARRAY)
            ->andReturn([['licence' => ['id' => 123]]]);

        $this->assertSame([123], $this->sut->fetchLicenceIdsForContinuationAndLicences(111, [222, 333]));

        $this->assertSame(
            'SELECT m, l, w0, w1' . self::FROM . ' LEFT JOIN m.licence l'
            . ' LEFT JOIN m.status w0 LEFT JOIN m.signatureType w1'
            . ' WHERE m.licence IN(:licences) AND m.continuation = :continuation',
            $qb->getDQL(),
        );
        $this->assertSame([222, 333], $qb->getParameter('licences')->getValue());
        $this->assertSame(111, $qb->getParameter('continuation')->getValue());
    }

    public function testCreateContinuationDetails(): void
    {
        $query = m::mock();
        $query->expects('executeInsert')->with([1, 2], 0, 'status', 7)->andReturn('RESULT');

        $this->dbQueryService->expects('get')
            ->with('Continuations\CreateContinuationDetails')
            ->andReturn($query);

        $this->assertSame('RESULT', $this->sut->createContinuationDetails([1, 2], 0, 'status', 7));
    }

    /**
     * Digital reminders go out for licences expiring inside the window whose notification has
     * been sent but whose reminder has not.
     */
    public function testFetchListForDigitalReminders(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForDigitalReminders(14));

        $this->assertSame(
            'SELECT m, w0, w1, c, l' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.continuation c LEFT JOIN m.licence l'
            . " WHERE l.status IN('" . implode("', '", self::ACTIVE_LICENCE_STATUSES) . "')"
            . ' AND l.expiryDate >= :NOW AND l.expiryDate <= :maxExpiryDate'
            . " AND m.status NOT IN('" . Entity::STATUS_COMPLETE . "')"
            . ' AND c.month = MONTH(l.expiryDate) AND c.year = YEAR(l.expiryDate)'
            . ' AND m.digitalNotificationSent = 1 AND m.digitalReminderSent = 0',
            $qb->getDQL(),
        );
        $this->assertSame(new \DateTime()->format('Y-m-d'), $qb->getParameter('NOW')->getValue());
    }

    private function continuationDetailWithFee(string $feeType, string $feeStatus): Entity|m\MockInterface
    {
        $fee = m::mock(FeeEntity::class);
        $fee->shouldReceive('getFeeType->getFeeType->getId')->andReturn($feeType);
        $fee->shouldReceive('getFeeStatus->getId')->andReturn($feeStatus);

        $detail = m::mock(Entity::class);
        $detail->shouldReceive('getLicence->getFees')->andReturn(new ArrayCollection([$fee]));

        return $detail;
    }
}
