<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Domain\Exception\VersionConflictException;
use Dvsa\Olcs\Api\Domain\Repository\Application as Repo;
use Dvsa\Olcs\Api\Entity\Application\Application as Entity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Mockery as m;

final class ApplicationTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' a';

    /** withRefdata() joins eleven RefData associations on Application. */
    private const string REFDATA_SELECT = 'a, w0, w1, w2, w3, w4, w5, w6, w7, w8, w9, w10';

    private const string REFDATA_JOINS = ' LEFT JOIN a.status w0 LEFT JOIN a.variationType w1'
        . ' LEFT JOIN a.psvWhichVehicleSizes w2 LEFT JOIN a.licenceType w3'
        . ' LEFT JOIN a.goodsOrPsv w4 LEFT JOIN a.vehicleType w5'
        . ' LEFT JOIN a.grantAuthority w6 LEFT JOIN a.withdrawnReason w7'
        . ' LEFT JOIN a.interimStatus w8 LEFT JOIN a.appliedVia w9'
        . ' LEFT JOIN a.signatureType w10';

    /** The organisation filter is a join condition, not a WHERE clause. */
    private const string ORG_JOIN = ' INNER JOIN a.licence l WITH l.organisation = :organisationId';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('wrongEntityProvider')]
    public function testRejectsEntitiesOfTheWrongType(string $method): void
    {
        $this->expectException(RuntimeException::class);

        $this->sut->{$method}(m::mock(Licence::class), 1);
    }

    public static function wrongEntityProvider(): \Iterator
    {
        yield 'lock' => ['lock'];
        yield 'save' => ['save'];
        yield 'delete' => ['delete'];
    }

    public function testLock(): void
    {
        $entity = m::mock(Entity::class);

        $this->em->expects('lock')->with($entity, LockMode::OPTIMISTIC, 1);

        $this->sut->lock($entity, 1);

        $this->assertTrue(true);
    }

    public function testLockWithConflict(): void
    {
        $entity = m::mock(Entity::class);

        $this->em->expects('lock')
            ->with($entity, LockMode::OPTIMISTIC, 1)
            ->andThrow(OptimisticLockException::class);

        $this->expectException(VersionConflictException::class);

        $this->sut->lock($entity, 1);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('persistenceProvider')]
    public function testPersistence(string $method, string $expectedEmCall): void
    {
        $entity = m::mock(Entity::class);

        $this->em->expects($expectedEmCall)->with($entity);
        $this->em->expects('flush');

        $this->sut->{$method}($entity);

        $this->assertTrue(true);
    }

    public static function persistenceProvider(): \Iterator
    {
        yield 'save' => ['save', 'persist'];
        yield 'delete' => ['delete', 'remove'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('referenceProvider')]
    public function testGetReference(string $method, string $expectedClass): void
    {
        $reference = m::mock($expectedClass);

        $this->em->expects('getReference')->with($expectedClass, 'foo')->andReturn($reference);

        $this->assertSame($reference, $this->sut->{$method}('foo'));
    }

    public static function referenceProvider(): \Iterator
    {
        yield 'refdata' => ['getRefdataReference', RefData::class];
        yield 'category' => ['getCategoryReference', Category::class];
        yield 'sub category' => ['getSubCategoryReference', SubCategory::class];
    }

    public function testFetchUsingId(): void
    {
        $result = m::mock(Entity::class);

        $query = m::mock(TransferQry\QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(1);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$result]);
        $this->em->expects('lock')->with($result, LockMode::OPTIMISTIC, 1);

        $this->assertSame($result, $this->sut->fetchUsingId($query, Query::HYDRATE_OBJECT, 1));

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . ', w11' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN a.licence w11'
            . ' WHERE a.id = :byId',
            $qb->getDQL(),
        );
    }

    /**
     * Pulls both the licence's and the application's operating centres in one query, so the two
     * sets can be compared without a second round trip.
     */
    public function testFetchWithLicenceAndOc(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchWithLicenceAndOc(1));

        $this->assertSame(
            'SELECT a, l, l_oc, l_oc_oc, l_oc_oc_a, a_oc, a_oc_oc, a_oc_oc_a, l_ea' . self::FROM
            . ' LEFT JOIN a.licence l LEFT JOIN l.operatingCentres l_oc'
            . ' LEFT JOIN l_oc.operatingCentre l_oc_oc LEFT JOIN l_oc_oc.address l_oc_oc_a'
            . ' LEFT JOIN a.operatingCentres a_oc LEFT JOIN a_oc.operatingCentre a_oc_oc'
            . ' LEFT JOIN a_oc_oc.address a_oc_oc_a LEFT JOIN l.enforcementArea l_ea'
            . ' WHERE a.id = :byId',
            $qb->getDQL(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('withLicenceProvider')]
    public function testFetchWithLicenceVariants(string $method, string $expectedSelect, string $expectedJoins): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->{$method}(1));

        $this->assertSame(
            'SELECT ' . $expectedSelect . self::FROM . $expectedJoins . ' WHERE a.id = :byId',
            $qb->getDQL(),
        );
    }

    public static function withLicenceProvider(): \Iterator
    {
        yield 'licence only' => ['fetchWithLicence', 'a, l', ' LEFT JOIN a.licence l'];
        yield 'licence and organisation' => [
            'fetchWithLicenceAndOrg',
            'a, l, l_org',
            ' LEFT JOIN a.licence l LEFT JOIN l.organisation l_org',
        ];
    }

    public function testFetchWithLicenceNotFound(): void
    {
        $this->createRealQb()->willReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchWithLicence(1);
    }

    public function testFetchWithTmLicences(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchWithTmLicences(1));

        $this->assertSame(
            'SELECT a, l, ltml' . self::FROM
            . ' LEFT JOIN a.licence l LEFT JOIN l.tmLicences ltml'
            . ' WHERE a.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testFetchActiveForOrganisation(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchActiveForOrganisation(7));

        // The statuses are inlined into the IN() rather than bound.
        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . self::FROM . self::REFDATA_JOINS . self::ORG_JOIN
            . " WHERE a.status IN('" . Entity::APPLICATION_STATUS_UNDER_CONSIDERATION
            . "', '" . Entity::APPLICATION_STATUS_GRANTED . "')",
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('organisationId')->getValue());
    }

    /**
     * New applications always count; variations only count against a strictly active licence and
     * only when they are not a director-change variation.
     */
    public function testFetchByOrgAndStatusForActiveLicences(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->andReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchByOrgAndStatusForActiveLicences(7, ['apsts_new']),
        );

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . self::FROM . self::REFDATA_JOINS . self::ORG_JOIN
            . " WHERE a.status IN('apsts_new')"
            . ' AND (a.isVariation = 0 OR (l.status IN('
            . "'" . Licence::LICENCE_STATUS_VALID . "', '" . Licence::LICENCE_STATUS_SUSPENDED
            . "', '" . Licence::LICENCE_STATUS_CURTAILED . "')"
            . ' AND a.isVariation = 1'
            . ' AND (a.variationType IS NULL OR a.variationType <> :directorChangeVariationType)))',
            $qb->getDQL(),
        );
        $this->assertSame(
            Entity::VARIATION_TYPE_DIRECTOR_CHANGE,
            $qb->getParameter('directorChangeVariationType')->getValue(),
        );
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        // applyListJoins() omits modifyQuery(); fetchList() points the helper here first.
        $this->queryBuilder->modifyQuery($qb);

        $this->sut->applyListJoins($qb);

        $this->assertSame('SELECT a, l' . self::FROM . ' LEFT JOIN a.licence l', $qb->getDQL());
    }

    /**
     * Director-change variations are excluded from every list, whatever else is filtered.
     */
    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = TransferQry\Application\GetList::create([
            'organisation' => 7,
            'status' => 'apsts_new',
        ]);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT a' . self::FROM
            . ' WHERE l.organisation = :organisation AND a.status = :STATUS'
            . ' AND (a.isVariation = :isVariation'
            . " OR COALESCE(IDENTITY(a.variationType), '') <> :variationType)",
            $qb->getDQL(),
        );
        $this->assertFalse($qb->getParameter('isVariation')->getValue());
        $this->assertSame(
            Entity::VARIATION_TYPE_DIRECTOR_CHANGE,
            $qb->getParameter('variationType')->getValue(),
        );
    }

    /**
     * NTU candidates are granted applications still carrying an outstanding grant fee.
     */
    public function testFetchForNtu(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForNtu());

        $this->assertSame(
            'SELECT a, l, lta, f, ft, fs' . self::FROM
            . ' LEFT JOIN a.licence l LEFT JOIN l.trafficArea lta LEFT JOIN a.fees f'
            . ' LEFT JOIN f.feeType ft LEFT JOIN f.feeStatus fs'
            . ' WHERE a.status = :appStatus AND f.feeStatus = :feeStatus'
            . ' AND ft.feeType IN(:feeType)',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::APPLICATION_STATUS_GRANTED, $qb->getParameter('appStatus')->getValue());
        $this->assertSame(FeeEntity::STATUS_OUTSTANDING, $qb->getParameter('feeStatus')->getValue());
        $this->assertSame(
            [FeeTypeEntity::FEE_TYPE_GRANT, FeeTypeEntity::FEE_TYPE_GRANTVAR],
            $qb->getParameter('feeType')->getValue(),
        );
    }

    public function testFetchAbandonedVariations(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchAbandonedVariations('2019-01-01'));

        $this->assertSame(
            'SELECT a' . self::FROM
            . ' WHERE a.isVariation = :isVariation AND a.variationType = :variationType'
            . ' AND a.status = :status AND a.createdOn < :olderThanDate',
            $qb->getDQL(),
        );
        $this->assertSame(
            Entity::APPLICATION_STATUS_NOT_SUBMITTED,
            $qb->getParameter('status')->getValue(),
        );
    }

    public function testFetchOpenApplicationsForLicence(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchOpenApplicationsForLicence(7));

        $this->assertSame(
            'SELECT a' . self::FROM
            . ' WHERE a.licence = :licenceId AND a.status = :applicationStatus',
            $qb->getDQL(),
        );
        $this->assertSame(
            Entity::APPLICATION_STATUS_UNDER_CONSIDERATION,
            $qb->getParameter('applicationStatus')->getValue(),
        );
    }

    /**
     * The interim cutoff is midnight today, so an interim ending earlier today still counts.
     */
    public function testFetchOpenApplicationsWhereInterimInForceAndInterimEndDateIsPast(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(
            ['RESULTS'],
            $this->sut->fetchOpenApplicationsWhereInterimInForceAndInterimEndDateIsPast(),
        );

        $this->assertSame(
            'SELECT a' . self::FROM
            . ' WHERE a.status = :applicationStatus AND a.interimStatus = :interimStatus'
            . ' AND a.interimEnd < :interimEnd',
            $qb->getDQL(),
        );
        $this->assertSame(
            new \DateTime()->format('Y-m-d') . ' 00:00:00',
            $qb->getParameter('interimEnd')->getValue()->format('Y-m-d H:i:s'),
        );
    }
}
