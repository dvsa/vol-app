<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\LockMode;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Query\Licence\InternationalGoodsReport;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\Address as AddressEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact as PhoneContactEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Licence::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\AbstractRepository::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\AbstractReadonlyRepository::class)]
class LicenceTest extends RepositoryTestCase
{
    /** @var LicenceRepo | m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(LicenceRepo::class, true);
    }

    public function testFetchSafetyDetailsUsingId(): void
    {
        /** @var QueryInterface | m\MockInterface $command */
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')
            ->andReturn(111);

        /** @var QueryBuilder | m\MockInterface $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')->once()->with(Query::HYDRATE_OBJECT)->andReturn(null);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with(111)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('workshops', 'w')
            ->andReturnSelf()
            ->shouldReceive('withContactDetails')
            ->once();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Licence::class)
            ->andReturn($repo);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchSafetyDetailsUsingId($command, Query::HYDRATE_OBJECT, 1);
    }

    public function testFetchSafetyDetailsUsingIdWithResults(): void
    {
        /** @var QueryInterface | m\MockInterface $command */
        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')
            ->andReturn(111);

        $result = m::mock(Licence::class);
        $results = [$result];

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('byId')
            ->once()
            ->with(111)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('workshops', 'w')
            ->andReturnSelf()
            ->shouldReceive('withContactDetails')
            ->once();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Licence::class)
            ->andReturn($repo)
            ->shouldReceive('lock')
            ->with($result, LockMode::OPTIMISTIC, 1);

        $this->sut->fetchSafetyDetailsUsingId($command, Query::HYDRATE_OBJECT, 1);
    }

    /**
     * Test existsByLicNo method
     *
     * @param array $result
     * @param bool $licenceFound
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('existsByLicNoProvider')]
    public function testExistsByLicNo(mixed $result, mixed $licenceFound): void
    {
        $licNo = 'OB1234567';
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);
        $doctrineComparison = m::mock(Comparison::class);

        $this->em->shouldReceive('getRepository')->with(Licence::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $qb->shouldReceive('expr->eq')->with('m.licNo', ':licNo')->once()->andReturn($doctrineComparison);
        $qb->shouldReceive('where')->with($doctrineComparison)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licNo', $licNo)->once()->andReturnSelf();
        $qb->shouldReceive('setMaxResults')->with(1)->once()->andReturnSelf();
        $qb->shouldReceive('getQuery->getResult')->with()->once()->andReturn($result);

        $this->assertSame($licenceFound, $this->sut->existsByLicNo($licNo));
    }

    /**
     * Data provider for testExistsByLicNo
     *
     * @return array
     */
    public static function existsByLicNoProvider(): array
    {
        return [
            [[0 => 'Result'], true],
            [[], false]
        ];
    }

    /**
     * Tests finding a licence by licNo without retreiving the additional data
     */
    public function testFetchByLicNoWithoutAdditionalData(): void
    {
        $licNo = 'OB1234567';
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);
        $doctrineComparison = m::mock(Comparison::class);

        $this->em->shouldReceive('getRepository')->with(Licence::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $qb->shouldReceive('expr->eq')->with('m.licNo', ':licNo')->once()->andReturn($doctrineComparison);
        $qb->shouldReceive('where')->with($doctrineComparison)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licNo', $licNo)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getOneOrNullResult')->with()->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByLicNoWithoutAdditionalData($licNo));
    }

    /**
     * Tests exception thrown when returned licence record is null
     */
    public function testFetchByLicNoWithoutAdditionalDataNotFound(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $licNo = 'OB1234567';
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);
        $doctrineComparison = m::mock(Comparison::class);

        $this->em->shouldReceive('getRepository')->with(Licence::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $qb->shouldReceive('expr->eq')->with('m.licNo', ':licNo')->once()->andReturn($doctrineComparison);
        $qb->shouldReceive('where')->with($doctrineComparison)->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licNo', $licNo)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getOneOrNullResult')->with()->once()->andReturn(null);

        $this->sut->fetchByLicNoWithoutAdditionalData($licNo);
    }

    public function testFetchByLicNo(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);

        $this->em->shouldReceive('getRepository')->with(Licence::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();

        $this->queryBuilder->shouldReceive('with')->with('operatingCentres', 'ocs')->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('ocs.operatingCentre', 'ocs_oc')->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('ocs_oc.address', 'ocs_oc_a')->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.licNo', ':licNo')->once()->andReturn('EXPR');
        $qb->shouldReceive('where')->with('EXPR')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licNo', 'LIC0001')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->with()->once()->andReturn(['RESULTS']);

        $this->assertSame('RESULTS', $this->sut->fetchByLicNo('LIC0001'));
    }

    public function testFetchByLicNoNotFound(): void
    {
        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);

        $this->em->shouldReceive('getRepository')->with(Licence::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();

        $this->queryBuilder->shouldReceive('with')->with('operatingCentres', 'ocs')->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('ocs.operatingCentre', 'ocs_oc')->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('ocs_oc.address', 'ocs_oc_a')->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.licNo', ':licNo')->once()->andReturn('EXPR');
        $qb->shouldReceive('where')->with('EXPR')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licNo', 'LIC0001')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->with()->once()->andReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchByLicNo('LIC0001');
    }

    public function testFetchForUserRegistration(): void
    {
        $licNo = 'LIC0001';

        $address = m::mock(AddressEntity::class)->makePartial();
        $address->setAddressLine1('a1');

        $cd = m::mock(ContactDetailsEntity::class)->makePartial();
        $cd->setAddress($address);

        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setOrganisationUsers(new ArrayCollection([]));

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setCorrespondenceCd($cd);
        $licence->setOrganisation($org);

        $this->sut->shouldReceive('fetchByLicNo')->with($licNo)->once()->andReturn($licence);

        $this->assertSame($licence, $this->sut->fetchForUserRegistration($licNo));
    }

    public function testFetchForUserRegistrationThrowsIncorrectAddressException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $licNo = 'LIC0001';

        $address = m::mock(AddressEntity::class)->makePartial();

        $cd = m::mock(ContactDetailsEntity::class)->makePartial();
        $cd->setAddress($address);

        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setOrganisationUsers(new ArrayCollection([]));

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setCorrespondenceCd($cd);
        $licence->setOrganisation($org);

        $this->sut->shouldReceive('fetchByLicNo')->with($licNo)->once()->andReturn($licence);

        $this->sut->fetchForUserRegistration($licNo);
    }

    public function testFetchForUserRegistrationThrowsUnlicencedException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $licNo = 'LIC0001';

        $address = m::mock(AddressEntity::class)->makePartial();
        $address->setAddressLine1('a1');

        $cd = m::mock(ContactDetailsEntity::class)->makePartial();
        $cd->setAddress($address);

        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setOrganisationUsers(new ArrayCollection([]));
        $org->setIsUnlicensed(true);

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setCorrespondenceCd($cd);
        $licence->setOrganisation($org);

        $this->sut->shouldReceive('fetchByLicNo')->with($licNo)->once()->andReturn($licence);

        $this->sut->fetchForUserRegistration($licNo);
    }

    public function testFetchForUserRegistrationThrowsAdminUsersException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $licNo = 'LIC0001';

        $address = m::mock(AddressEntity::class)->makePartial();
        $address->setAddressLine1('a1');

        $cd = m::mock(ContactDetailsEntity::class)->makePartial();
        $cd->setAddress($address);

        $orgUser = m::mock();

        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->shouldReceive('getAdminOrganisationUsers')->once()->andReturn(new ArrayCollection([$orgUser]));

        $licence = m::mock(Licence::class)->makePartial();
        $licence->setCorrespondenceCd($cd);
        $licence->setOrganisation($org);

        $this->sut->shouldReceive('fetchByLicNo')->with($licNo)->once()->andReturn($licence);

        $this->sut->fetchForUserRegistration($licNo);
    }

    public function testFetchByVrm(): void
    {
        $qb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByVrm('ABC123'));

        $expectedQuery = '[QUERY] INNER JOIN m.licenceVehicles lv INNER JOIN lv.vehicle v'
            . ' AND lv.removalDate IS NULL AND v.vrm = [[ABC123]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchByVrmAndStatus(): void
    {
        $qb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchByVrm('ABC123', true));

        $expectedQuery = '[QUERY] INNER JOIN m.licenceVehicles lv ' .
            'INNER JOIN lv.vehicle v AND lv.removalDate IS NULL AND v.vrm = [[ABC123]] ' .
            'INNER JOIN lv.application a AND a.status NOT IN ' .
            '["apsts_cancelled","apsts_refused","apsts_withdrawn","apsts_ntu"]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchWithEnforcementArea(): void
    {
        $licenceId = 1;

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('getQuery->getSingleResult')
            ->andReturn('RESULT');

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('enforcementArea')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($licenceId)
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Licence::class)
            ->andReturn($repo);

        $result = $this->sut->fetchWithEnforcementArea($licenceId);
        $this->assertEquals('RESULT', $result);
    }

    public function testFetchWithOperatingCentres(): void
    {
        $licenceId = 1;

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('getQuery->getSingleResult')
            ->andReturn('RESULT');

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('with')
            ->with('operatingCentres', 'oc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('oc.operatingCentre', 'oc_oc')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('with')
            ->with('oc_oc.address', 'oc_oc_a')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('byId')
            ->with($licenceId)
            ->once()
            ->andReturnSelf();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Licence::class)
            ->andReturn($repo);

        $result = $this->sut->fetchWithOperatingCentres($licenceId);
        $this->assertEquals('RESULT', $result);
    }

    public function testFetchWithPrivateHireLicence(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('privateHireLicences', 'phl')->once()->andReturnSelf()
            ->shouldReceive('with')->with('phl.contactDetails', 'cd')->once()->andReturnSelf()
            ->shouldReceive('with')->with('cd.address', 'add')->once()->andReturnSelf()
            ->shouldReceive('with')->with('add.countryCode')->once()->andReturnSelf()
            ->shouldReceive('byId')->with(21)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getSingleResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchWithPrivateHireLicence(21));

        $expectedQuery = 'BLAH';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testApplyListFilters(): void
    {
        $this->setUpSut(LicenceRepo::class, true);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('m.organisation', ':organisation')->once()->andReturn('EXPR1');
        $mockQb->shouldReceive('setParameter')->with('organisation', 723)->once()->andReturn();
        $mockQb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->notIn')->with('m.status', ':excludeStatuses')->once()->andReturn('EXPR2');
        $mockQb->shouldReceive('setParameter')->with('excludeStatuses', ['status1', 'status2'])->once()->andReturn();
        $mockQb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();

        $mockQuery = m::mock(QueryInterface::class);
        $mockQuery->shouldReceive('getOrganisation')->with()->andReturn(723);
        $mockQuery->shouldReceive('getExcludeStatuses')->with()->andReturn(['status1', 'status2']);

        $this->sut->applyListFilters($mockQb, $mockQuery);
    }

    public function testFetchForContinuation(): void
    {
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->once()->with($qb)->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('trafficArea', 'ta')->once()->andReturnSelf();

        $qb->shouldReceive('expr->gte')->with('m.expiryDate', ':expiryFrom')->once()->andReturn('condFrom');
        $qb->shouldReceive('andWhere')->with('condFrom')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('expiryFrom', m::type(\DateTime::class))->once()->andReturnSelf();

        $qb->shouldReceive('expr->lte')->with('m.expiryDate', ':expiryTo')->once()->andReturn('condTo');
        $qb->shouldReceive('andWhere')->with('condTo')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('expiryTo', m::type(\DateTime::class))->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('ta.id', ':trafficArea')->once()->andReturn('condTa');
        $qb->shouldReceive('andWhere')->with('condTa')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('trafficArea', 'B')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')
            ->andReturn('RESULT');

        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);
        $this->em->shouldReceive('getRepository')
            ->with(Licence::class)
            ->andReturn($repo);

        $result = $this->sut->fetchForContinuation(2015, 1, 'B');
        $this->assertEquals('RESULT', $result);
    }

    public function testFetchForContinuationNotSought(): void
    {
        $qb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($qb);

        $results = [
            [
                'id' => 1,
                'version' => 2,
                'licNo' => 'foo',
                'trafficArea' => [
                    'name' => 'bar'
                ]
            ]
        ];
        $expected = [
            [
                'id' => 1,
                'version' => 2,
                'licNo' => 'foo',
                'taName' => 'bar'
            ]
        ];
        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()
                ->shouldReceive('getResult')
                ->with(Query::HYDRATE_ARRAY)
                ->once()
                ->andReturn($results)
                ->getMock()
        );

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->andReturnSelf();

        $now = new DateTime();

        $this->assertEquals($expected, $this->sut->fetchForContinuationNotSought($now, 200));

        $expectedQuery = '[QUERY] ' .
            'SELECT m, ta ' .
            'AND m.expiryDate < [[' . $now->format(\DateTime::W3C) . ']] ' .
            'AND m.status IN [[["lsts_valid","lsts_curtailed","lsts_suspended"]]] ' .
            'AND (m.goodsOrPsv = [[lcat_gv]] OR (m.goodsOrPsv = [[lcat_psv]] AND m.licenceType = [[ltyp_sr]])) ' .
            'INNER JOIN m.fees f INNER JOIN f.feeType ft AND f.feeStatus = [[lfs_ot]] AND ft.feeType = [[CONT]] ' .
            'LIMIT 200';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchWithVariationsAndInterimInforce(): void
    {
        $licenceId = 1;
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->once()->with($qb)->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('applications', 'a')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('a.interimStatus', 'ais')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with($licenceId)->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('a.isVariation', true)->once()->andReturn('condVar');
        $qb->shouldReceive('andWhere')->with('condVar')->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('a.status', ':applicationStatus')->once()->andReturn('condApp');
        $qb->shouldReceive('andWhere')->with('condApp')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')
            ->with('applicationStatus', ApplicationEntity::APPLICATION_STATUS_UNDER_CONSIDERATION)
            ->once()
            ->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('a.interimStatus', ':interimStatus')->once()->andReturn('condInt');
        $qb->shouldReceive('andWhere')->with('condInt')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')
            ->with('interimStatus', ApplicationEntity::INTERIM_STATUS_INFORCE)
            ->once()
            ->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->andReturn(['result']);

        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->andReturn($qb);
        $this->em->shouldReceive('getRepository')
            ->with(Licence::class)
            ->andReturn($repo);

        $this->assertEquals(['result'], $this->sut->fetchWithVariationsAndInterimInforce($licenceId));
    }

    public function testFetchWithAddressesUsingIdWithQuery(): void
    {
        $id = 9999;

        $mockQb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($mockQb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($mockQb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('byId')->once()->with($id)->andReturnSelf()
            ->shouldReceive('withContactDetails')->once()->with('correspondenceCd', 'c')->andReturnSelf()
            ->shouldReceive('with')->once()->with('c.phoneContacts', 'c_p')->andReturnSelf()
            ->shouldReceive('with')->once()->with('c_p.phoneContactType', 'c_p_pct')->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->with(PhoneContactEntity::class, 'c_p')->andReturnSelf()
            ->shouldReceive('with')->once()->with('organisation', 'o')->andReturnSelf()
            ->shouldReceive('withContactDetails')->once()->with('o.contactDetails', 'o_cd')->andReturnSelf()
            ->shouldReceive('withContactDetails')->once()->with('establishmentCd', 'e')->andReturnSelf()
            ->shouldReceive('withContactDetails')->once()->with('transportConsultantCd', 't')->andReturnSelf()
            ->shouldReceive('with')->once()->with('t.phoneContacts', 't_p')->andReturnSelf()
            ->shouldReceive('with')->once()->with('t_p.phoneContactType', 't_p_pct')->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->with(PhoneContactEntity::class, 't_p')->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getSingleResult')
            ->with()
            ->once()
            ->andReturn(['EXPECT']);

        /** @var QueryInterface $mockQuery */
        $mockQuery = m::mock(QueryInterface::class)
            ->shouldReceive('getId')->with()->andReturn($id)
            ->getMock();

        static::assertEquals(['EXPECT'], $this->sut->fetchWithAddressesUsingId($mockQuery));
    }

    public function testFetchWithAddressesUsingIdWithInt(): void
    {
        $id = 9999;

        $mockQb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($mockQb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($mockQb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('byId')->once()->with($id)->andReturnSelf()
            ->shouldReceive('withContactDetails')->once()->with('correspondenceCd', 'c')->andReturnSelf()
            ->shouldReceive('with')->once()->with('c.phoneContacts', 'c_p')->andReturnSelf()
            ->shouldReceive('with')->once()->with('c_p.phoneContactType', 'c_p_pct')->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->with(PhoneContactEntity::class, 'c_p')->andReturnSelf()
            ->shouldReceive('with')->once()->with('organisation', 'o')->andReturnSelf()
            ->shouldReceive('withContactDetails')->once()->with('o.contactDetails', 'o_cd')->andReturnSelf()
            ->shouldReceive('withContactDetails')->once()->with('establishmentCd', 'e')->andReturnSelf()
            ->shouldReceive('withContactDetails')->once()->with('transportConsultantCd', 't')->andReturnSelf()
            ->shouldReceive('with')->once()->with('t.phoneContacts', 't_p')->andReturnSelf()
            ->shouldReceive('with')->once()->with('t_p.phoneContactType', 't_p_pct')->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->with(PhoneContactEntity::class, 't_p')->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getSingleResult')
            ->with()
            ->once()
            ->andReturn(['EXPECT']);

        static::assertEquals(['EXPECT'], $this->sut->fetchWithAddressesUsingId($id));
    }

    public function testFetchByOrganisationIdAndStatuses(): void
    {

        $qb = m::mock(QueryBuilder::class);
        $repo = m::mock(EntityRepository::class);

        $statuses = ['foo', 'bar'];

        $this->em->shouldReceive('getRepository')->with(Licence::class)->andReturn($repo);

        $repo->shouldReceive('createQueryBuilder')->with('m')->once()->andReturn($qb);

        $qb->shouldReceive('expr->eq')->with('m.organisation', ':organisationId')->once()->andReturn('orgCond');
        $qb->shouldReceive('andWhere')->with('orgCond')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('organisationId', 1)->once()->andReturnSelf();

        $qb->shouldReceive('expr->in')->with('m.status', ':statuses')->once()->andReturn('stCond');
        $qb->shouldReceive('andWhere')->with('stCond')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('statuses', $statuses)->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_ARRAY)->once()->andReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchByOrganisationIdAndStatuses(1, $statuses));
    }

    public function testFetchByOrganisationId(): void
    {
        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_ARRAY)->once()->andReturn(['RESULTS']);

        $this->assertEquals(['RESULTS'], $this->sut->fetchByOrganisationId(2017));

        $expectedQuery = 'BLAH AND m.organisation = [[2017]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchPsvLicenceIdsToSurrender(): void
    {
        $qb = m::mock(QueryBuilder::class);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf();

        $licTypes = [
            Licence::LICENCE_TYPE_RESTRICTED,
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
        ];

        $statuses = [
            Licence::LICENCE_STATUS_VALID,
            Licence::LICENCE_STATUS_CURTAILED,
            Licence::LICENCE_STATUS_SUSPENDED,
        ];

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Licence::class)
            ->andReturn($repo);

        $qb->shouldReceive('expr->lt')->with('m.expiryDate', ':now')->once()->andReturn('EXPR1');
        $qb->shouldReceive('andWhere')->with('EXPR1')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('now', m::type(DateTime::class))->once()->andReturnSelf();

        $qb->shouldReceive('expr->eq')->with('m.goodsOrPsv', ':psv')->once()->andReturn('EXPR2');
        $qb->shouldReceive('andWhere')->with('EXPR2')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('psv', Licence::LICENCE_CATEGORY_PSV)->once()->andReturnSelf();

        $qb->shouldReceive('expr->in')->with('m.licenceType', ':licTypes')->once()->andReturn('EXPR3');
        $qb->shouldReceive('andWhere')->with('EXPR3')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('licTypes', $licTypes)->once()->andReturnSelf();

        $qb->shouldReceive('expr->in')->with('m.status', ':statuses')->once()->andReturn('EXPR4');
        $qb->shouldReceive('andWhere')->with('EXPR4')->once()->andReturnSelf();
        $qb->shouldReceive('setParameter')->with('statuses', $statuses)->once()->andReturnSelf();

        $results = [
            [
                'id' => 1
            ],
            [
                'id' => 2
            ]
        ];

        $qb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_ARRAY)->once()->andReturn($results);

        $this->assertSame([1, 2], $this->sut->fetchPsvLicenceIdsToSurrender());
    }

    public function testInternationalGoodsReport(): void
    {
        $this->expectQueryWithData(InternationalGoodsReport::class, [], [], m::mock(Result::class));
        $this->sut->internationalGoodsReport();
    }

    public function testFetchForLastTmAutoLetter(): void
    {
        $qb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($qb);

        $this->em->shouldReceive('getFilters->isEnabled')->with('soft-deleteable')->andReturn(false);
        $qb->shouldReceive('getDQL')->times(2);
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULTS']);

        $this->sut->fetchForLastTmAutoLetter();

        $today = (new DateTime())
            ->setTime(0, 0, 0, 0)
            ->format('Y-m-d');

        $tomorrow = (new DateTime())
            ->add(new \DateInterval('P1D'))
            ->setTime(0, 0, 0, 0)
            ->format('Y-m-d H:i:s');

        $expectedQuery = '[QUERY] DISTINCT ' .
            'AND m.goodsOrPsv IN [[["lcat_gv","lcat_psv"]]] ' .
            'AND m.status IN [[["lsts_suspended","lsts_valid","lsts_curtailed"]]] ' .
            'AND m.licenceType IN [[["ltyp_sn","ltyp_si"]]] ' .
            'AND m.expiryDate >= [[' . $tomorrow . ']] ' .
            'AND tml.lastTmLetterDate IS NULL ' .
            'AND m.optOutTmLetter = 0 ' .
            'AND m.totAuthVehicles >= 1 ' .
            'INNER JOIN Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence tml WITH m.id = tml.licence ' .
            'SELECT IDENTITY(gp.licence) ' .
            'AND (gp.startDate <= [[' . $today . ']] ' .
            'AND gp.endDate >= [[' . $today . ']]) ' .
            'AND m.id NOT IN  ' .
            'SELECT IDENTITY(tml2.licence) ' .
            'AND (tml2.deletedDate >= [[' . $tomorrow . ']] ' .
            'OR tml2.deletedDate IS NULL) ' .
            'AND tml2.licence = m.id ' .
            'AND m.id NOT IN ';

        $this->assertEquals($expectedQuery, $this->query);
    }
}
