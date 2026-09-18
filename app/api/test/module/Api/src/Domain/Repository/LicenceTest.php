<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\FilterCollection;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as Repo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\GracePeriod as GracePeriodEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Entity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence as TMLicenceEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\OlcsTest\Support\TestQueryBuilder;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;
use Mockery as m;

final class LicenceTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    /** withRefdata() joins goodsOrPsv, vehicleType, licenceType, status and tachographIns. */
    private const string REFDATA_SELECT = 'm, w0, w1, w2, w3, w4';

    private const string REFDATA_JOINS = ' LEFT JOIN m.goodsOrPsv w0 LEFT JOIN m.vehicleType w1'
        . ' LEFT JOIN m.licenceType w2 LEFT JOIN m.status w3 LEFT JOIN m.tachographIns w4';

    private const array ACTIVE_STATUSES = [
        Entity::LICENCE_STATUS_VALID,
        Entity::LICENCE_STATUS_CURTAILED,
        Entity::LICENCE_STATUS_SUSPENDED,
    ];

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByCaseId(): void
    {
        $result = m::mock(Entity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$result]);
        $this->em->expects('lock')->with($result, LockMode::OPTIMISTIC, 1);

        $this->assertSame($result, $this->sut->fetchByCaseId(1, Query::HYDRATE_OBJECT, 1));

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . ', ta' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.trafficArea ta INNER JOIN m.cases c'
            . ' WHERE c.id = :caseId',
            $qb->getDQL(),
        );
    }

    public function testFetchSafetyDetailsById(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchSafetyDetailsById(1));

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . ', w, m_cd, m_cd_a, m_cd_a_cc, m_cd_pc, w5, w6'
            . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.workshops w LEFT JOIN w.contactDetails m_cd'
            . ' LEFT JOIN m_cd.address m_cd_a LEFT JOIN m_cd_a.countryCode m_cd_a_cc'
            . ' LEFT JOIN m_cd.phoneContacts m_cd_pc LEFT JOIN m_cd.contactType w5'
            . ' LEFT JOIN m_cd_pc.phoneContactType w6'
            . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testFetchSafetyDetailsByIdNotFound(): void
    {
        $this->createRealQb()->stubbedQuery()->expects('getResult')->andReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchSafetyDetailsById(1);
    }

    /**
     * The widest query in the repository: 37 selected aliases over 32 joins, four of them
     * duplicated (see the migration findings).
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('addressQueryProvider')]
    public function testFetchWithAddressesUsingId(mixed $argument): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchWithAddressesUsingId($argument));

        $this->assertStringStartsWith(
            'SELECT m, w0, w1, w2, w3, w4, c, c_a, c_a_cc, c_pc, w5, w6, c_p, c_p_pct, w7,'
            . ' o, o_cd, o_cd_a, o_cd_a_cc, o_cd_pc, w8, w9, e, e_a, e_a_cc, e_pc, w10, w11,'
            . ' t, t_a, t_a_cc, t_pc, w12, w13, t_p, t_p_pct, w14',
            $qb->getDQL(),
        );
        $this->assertStringEndsWith(' WHERE m.id = :byId', $qb->getDQL());
        $this->assertSame(1, $qb->getParameter('byId')->getValue());
    }

    public static function addressQueryProvider(): \Iterator
    {
        yield 'an int id' => [1];

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getId')->andReturn(1);
        yield 'a query object' => [$query];
    }

    public function testFetchByLicNo(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchByLicNo('OB123'));

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . ', ocs, ocs_oc, ocs_oc_a' . self::FROM
            . self::REFDATA_JOINS
            . ' LEFT JOIN m.operatingCentres ocs LEFT JOIN ocs.operatingCentre ocs_oc'
            . ' LEFT JOIN ocs_oc.address ocs_oc_a'
            . ' WHERE m.licNo = :licNo',
            $qb->getDQL(),
        );
        $this->assertSame('OB123', $qb->getParameter('licNo')->getValue());
    }

    public function testFetchByLicNoNotFound(): void
    {
        $this->createRealQb()->willReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchByLicNo('OB123');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('existsProvider')]
    public function testExistsByLicNo(array $results, bool $expected): void
    {
        $qb = $this->createRealQb()->willReturn($results);

        $this->assertSame($expected, $this->sut->existsByLicNo('OB123'));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.licNo = :licNo',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getMaxResults());
    }

    public static function existsProvider(): \Iterator
    {
        yield 'found' => [['a licence'], true];
        yield 'not found' => [[], false];
    }

    public function testFetchByLicNoWithoutAdditionalData(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByLicNoWithoutAdditionalData('OB123'));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.licNo = :licNo',
            $qb->getDQL(),
        );
    }

    public function testFetchByLicNoWithoutAdditionalDataNotFound(): void
    {
        $this->createRealQb()->stubbedQuery()->expects('getOneOrNullResult')->andReturnNull();

        $this->expectException(NotFoundException::class);

        $this->sut->fetchByLicNoWithoutAdditionalData('OB123');
    }

    /**
     * Registration is refused for a licence with no usable correspondence address, an unlicensed
     * organisation, or an organisation that already has an admin user.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('userRegistrationProvider')]
    public function testFetchForUserRegistration(
        bool $addressUsable,
        bool $unlicensed,
        bool $hasAdminUsers,
        ?string $expectedError,
    ): void {
        $address = m::mock(AddressEntity::class)->makePartial();
        $address->shouldReceive('isEmpty')->andReturn(!$addressUsable);

        $contactDetails = m::mock(ContactDetailsEntity::class)->makePartial();
        $contactDetails->shouldReceive('getAddress')->andReturn($address);

        $adminUsers = m::mock();
        $adminUsers->shouldReceive('isEmpty')->andReturn(!$hasAdminUsers);

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->shouldReceive('getIsUnlicensed')->andReturn($unlicensed);
        $organisation->shouldReceive('getAdminOrganisationUsers')->andReturn($adminUsers);

        $licence = m::mock(Entity::class)->makePartial();
        $licence->shouldReceive('getCorrespondenceCd')->andReturn($contactDetails);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);

        $this->sut->expects('fetchByLicNo')->with('OB123')->andReturn($licence);

        if ($expectedError !== null) {
            try {
                $this->sut->fetchForUserRegistration('OB123');
                $this->fail('Expected a ValidationException');
            } catch (ValidationException $e) {
                $this->assertSame(['licenceNumber' => [$expectedError]], $e->getMessages());
            }

            return;
        }

        $this->assertSame($licence, $this->sut->fetchForUserRegistration('OB123'));
    }

    public static function userRegistrationProvider(): \Iterator
    {
        yield 'accepted' => [true, false, false, null];
        yield 'no usable address' => [false, false, false, 'ERR_ADDRESS_NOT_FOUND'];
        yield 'unlicensed organisation' => [true, true, false, 'ERR_UNLICENCED_ORG'];
        yield 'organisation already has admin users' => [true, false, true, 'ERR_ADMIN_EXISTS'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('vrmProvider')]
    public function testFetchByVrm(bool $checkByStatus, string $expectedExtraJoin, string $expectedExtra): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByVrm('ABC123', $checkByStatus));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' INNER JOIN m.licenceVehicles lv INNER JOIN lv.vehicle v' . $expectedExtraJoin
            . ' WHERE lv.removalDate IS NULL AND v.vrm = :vrm' . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame('ABC123', $qb->getParameter('vrm')->getValue());
    }

    public static function vrmProvider(): \Iterator
    {
        yield 'any status' => [false, '', ''];
        // The excluded statuses are inlined into the NOT IN().
        yield 'excluding dead applications' => [
            true,
            ' INNER JOIN lv.application a',
            " AND a.status NOT IN('" . ApplicationEntity::APPLICATION_STATUS_CANCELLED
            . "', '" . ApplicationEntity::APPLICATION_STATUS_REFUSED
            . "', '" . ApplicationEntity::APPLICATION_STATUS_WITHDRAWN
            . "', '" . ApplicationEntity::APPLICATION_STATUS_NOT_TAKEN_UP . "')",
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('byIdProvider')]
    public function testFetchWithAssociations(
        string $method,
        string $expectedSelect,
        string $expectedJoins,
        bool $hydrated,
    ): void {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()
            ->expects('getSingleResult')
            ->with(...($hydrated ? [Query::HYDRATE_OBJECT] : []))
            ->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->{$method}(1));

        $this->assertSame(
            'SELECT ' . $expectedSelect . self::FROM . $expectedJoins . ' WHERE m.id = :byId',
            $qb->getDQL(),
        );
    }

    public static function byIdProvider(): \Iterator
    {
        yield 'enforcement area' => [
            'fetchWithEnforcementArea',
            'm, w0',
            ' LEFT JOIN m.enforcementArea w0',
            false,
        ];
        yield 'operating centres' => [
            'fetchWithOperatingCentres',
            'm, oc, oc_oc, oc_oc_a',
            ' LEFT JOIN m.operatingCentres oc LEFT JOIN oc.operatingCentre oc_oc'
            . ' LEFT JOIN oc_oc.address oc_oc_a',
            true,
        ];
        yield 'private hire licences' => [
            'fetchWithPrivateHireLicence',
            self::REFDATA_SELECT . ', phl, cd, add, w5',
            self::REFDATA_JOINS . ' LEFT JOIN m.privateHireLicences phl'
            . ' LEFT JOIN phl.contactDetails cd LEFT JOIN cd.address add'
            . ' LEFT JOIN add.countryCode w5',
            true,
        ];
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getOrganisation')->andReturn(723);
        $query->shouldReceive('getExcludeStatuses')->andReturn(['status1', 'status2']);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.organisation = :organisation AND m.status NOT IN(:excludeStatuses)',
            $qb->getDQL(),
        );
        $this->assertSame(723, $qb->getParameter('organisation')->getValue());
        $this->assertSame(['status1', 'status2'], $qb->getParameter('excludeStatuses')->getValue());
    }

    /**
     * The continuation window is the whole calendar month.
     */
    public function testFetchForContinuation(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForContinuation(2019, 2, 'B'));

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . ', ta' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.trafficArea ta'
            . ' WHERE m.expiryDate >= :expiryFrom AND m.expiryDate <= :expiryTo'
            . ' AND ta.id = :trafficArea',
            $qb->getDQL(),
        );
        $this->assertSame('2019-02-01', $qb->getParameter('expiryFrom')->getValue()->format('Y-m-d'));
        $this->assertSame('2019-02-28', $qb->getParameter('expiryTo')->getValue()->format('Y-m-d'));
    }

    /**
     * Continuation not sought: an expired active licence with an outstanding continuation fee,
     * restricted to goods licences and PSV special restricted.
     */
    public function testFetchForContinuationNotSought(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn([
            ['id' => 1, 'version' => 2, 'licNo' => 'OB123', 'trafficArea' => ['name' => 'B']],
        ]);

        $this->assertSame(
            [['id' => 1, 'version' => 2, 'licNo' => 'OB123', 'taName' => 'B']],
            $this->sut->fetchForContinuationNotSought(new \DateTime('2019-01-01'), 10),
        );

        // The explicit select() narrows to the root and traffic area, keeping the other joins
        // for filtering only — the comment in the repository cites memory_limit.
        $this->assertSame(
            'SELECT m, ta' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.licenceVehicles lv LEFT JOIN lv.goodsDiscs gd'
            . ' LEFT JOIN m.psvDiscs pd LEFT JOIN m.trafficArea ta'
            . ' INNER JOIN m.fees f INNER JOIN f.feeType ft'
            . ' WHERE m.expiryDate < :now AND m.status IN(:statuses)'
            . ' AND (m.goodsOrPsv = :gv OR (m.goodsOrPsv = :psv AND m.licenceType = :sr))'
            . ' AND f.feeStatus = :feeStatus AND ft.feeType = :feeType',
            $qb->getDQL(),
        );
        $this->assertSame(self::ACTIVE_STATUSES, $qb->getParameter('statuses')->getValue());
        $this->assertSame(FeeEntity::STATUS_OUTSTANDING, $qb->getParameter('feeStatus')->getValue());
        $this->assertSame(FeeTypeEntity::FEE_TYPE_CONT, $qb->getParameter('feeType')->getValue());
        $this->assertSame(10, $qb->getMaxResults());
    }

    public function testFetchPsvLicenceIdsToSurrender(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn([
            ['id' => 1],
            ['id' => 2],
        ]);

        $this->assertSame([1, 2], $this->sut->fetchPsvLicenceIdsToSurrender(new \DateTime('2019-01-01')));

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . self::FROM . self::REFDATA_JOINS
            . ' WHERE m.expiryDate < :now AND m.goodsOrPsv = :psv'
            . ' AND m.licenceType IN(:licTypes) AND m.status IN(:statuses)',
            $qb->getDQL(),
        );
        $this->assertSame(Entity::LICENCE_CATEGORY_PSV, $qb->getParameter('psv')->getValue());
        $this->assertSame(
            [
                Entity::LICENCE_TYPE_RESTRICTED,
                Entity::LICENCE_TYPE_STANDARD_NATIONAL,
                Entity::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            ],
            $qb->getParameter('licTypes')->getValue(),
        );
    }

    public function testFetchWithVariationsAndInterimInforce(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchWithVariationsAndInterimInforce(1));

        $this->assertSame(
            'SELECT ' . self::REFDATA_SELECT . ', a, ais' . self::FROM . self::REFDATA_JOINS
            . ' LEFT JOIN m.applications a LEFT JOIN a.interimStatus ais'
            . ' WHERE m.id = :byId AND a.isVariation = 1'
            . ' AND a.status = :applicationStatus AND a.interimStatus = :interimStatus',
            $qb->getDQL(),
        );
        $this->assertSame(
            ApplicationEntity::INTERIM_STATUS_INFORCE,
            $qb->getParameter('interimStatus')->getValue(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('byOrganisationProvider')]
    public function testFetchByOrganisation(string $method, array $args, string $expectedExtra): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}(...$args));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.organisation = :organisationId' . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('organisationId')->getValue());
    }

    public static function byOrganisationProvider(): \Iterator
    {
        yield 'any status' => ['fetchByOrganisationId', [7], ''];
        yield 'given statuses' => [
            'fetchByOrganisationIdAndStatuses',
            [7, ['lsts_valid']],
            ' AND m.status IN(:statuses)',
        ];
    }

    public function testInternationalGoodsReport(): void
    {
        $result = m::mock(Result::class);

        $query = m::mock();
        $query->expects('execute')->with([])->andReturn($result);

        $this->dbQueryService->expects('get')
            ->with(\Dvsa\Olcs\Api\Domain\Repository\Query\Licence\InternationalGoodsReport::class)
            ->andReturn($query);

        $this->assertSame($result, $this->sut->internationalGoodsReport());
    }

    public function testFetchForLastTmAutoLetterRejectsAnInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->sut->fetchForLastTmAutoLetter(99);
    }

    /**
     * The auto-letter query is the largest in the repository: two correlated NOT IN sub-selects,
     * a soft-delete override, and a second-letter branch that adds a MAX(deletedDate) correlation.
     * Asserting that it compiles proves every alias and field in it actually resolves.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('letterTypeProvider')]
    public function testFetchForLastTmAutoLetter(int $letterType, array $expectedFragments): void
    {
        $this->expectSoftDeleteableDisabledFor(TMLicenceEntity::class);

        $qb = $this->wireAutoLetterQueryBuilders();
        $qb->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForLastTmAutoLetter($letterType));

        $dql = $qb->getDQL();

        $this->assertStringStartsWith('SELECT DISTINCT m' . self::FROM, $dql);
        $this->assertStringContainsString(
            ' INNER JOIN ' . TMLicenceEntity::class . ' tml WITH m.id = tml.licence',
            $dql,
        );
        $this->assertStringContainsString(' tml.lastTmLetterDate IS NULL', $dql);
        $this->assertStringContainsString(' m.optOutTmLetter = 0', $dql);
        $this->assertStringContainsString(' m.totAuthVehicles >= 1', $dql);

        // Neither correlated sub-select leaks its own predicates into the outer WHERE.
        $this->assertStringContainsString(
            '(m.id NOT IN(SELECT IDENTITY(gp.licence) FROM ' . GracePeriodEntity::class . ' gp'
            . ' WHERE gp.startDate <= :today AND gp.endDate >= :today))',
            $dql,
        );
        $this->assertStringContainsString(
            '(m.id NOT IN(SELECT IDENTITY(tml2.licence) FROM ' . TMLicenceEntity::class . ' tml2'
            . ' WHERE (tml2.deletedDate >= :tomorrow OR tml2.deletedDate IS NULL)'
            . ' AND tml2.licence = m.id))',
            $dql,
        );

        foreach ($expectedFragments as $fragment) {
            $this->assertStringContainsString($fragment, $dql);
        }

        $this->compileDql($dql);
    }

    public static function letterTypeProvider(): \Iterator
    {
        yield 'first letter' => [Repo::LETTER_FIRST, [' tml.lastTmFirstEmailDate IS NULL']];
        yield 'second letter' => [
            Repo::LETTER_SECOND,
            [
                ' tml.lastTmFirstEmailDate IS NOT NULL',
                '(tml.deletedDate = (SELECT MAX(t2.deletedDate) FROM ' . TMLicenceEntity::class . ' t2'
                . ' WHERE t2.licence = m.id AND t2.deletedDate IS NOT NULL))',
                ' tml.deletedDate <= :date28DaysAgo',
            ],
        ];
    }

    /**
     * fetchForLastTmAutoLetter() builds three more query builders off the EntityManager, so each
     * has to be handed out separately — sharing one would let the sub-selects write their
     * predicates into the outer query.
     *
     * @return TestQueryBuilder the root builder
     */
    private function wireAutoLetterQueryBuilders(): TestQueryBuilder
    {
        $builders = [
            Entity::class => ['m' => $this->newRealQb()],
            GracePeriodEntity::class => ['gp' => $this->newRealQb()],
            TMLicenceEntity::class => ['tml2' => $this->newRealQb(), 't2' => $this->newRealQb()],
        ];

        foreach ($builders as $entity => $byAlias) {
            $repository = m::mock(EntityRepository::class);

            foreach ($byAlias as $alias => $qb) {
                $qb->select($alias)->from($entity, $alias);
                $repository->shouldReceive('createQueryBuilder')->with($alias)->andReturn($qb);
            }

            $this->em->shouldReceive('getRepository')->with($entity)->andReturn($repository);
        }

        return $this->qb = $builders[Entity::class]['m'];
    }

    private function expectSoftDeleteableDisabledFor(string $entityClass): void
    {
        $filter = m::mock(SoftDeleteableFilter::class);
        $filter->shouldReceive('disableForEntity')->with($entityClass);

        $filters = m::mock(FilterCollection::class);
        $filters->shouldReceive('isEnabled')->with('soft-deleteable')->andReturnTrue();
        $filters->shouldReceive('getFilter')->with('soft-deleteable')->andReturn($filter);

        $this->em->shouldReceive('getFilters')->withNoArgs()->andReturn($filters);
    }
}
