<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\EntityManager;
use Dvsa\Olcs\Api\Domain\Query\User\UserListInternalByTrafficArea;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Repository\User as Repo;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails as ContactDetailsEntity;
use Dvsa\Olcs\Api\Entity\User\Role as RoleEntity;
use Dvsa\Olcs\Api\Entity\User\Team as TeamEntity;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager as TransportManagerEntity;
use Mockery as m;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;

/**
 * @see Repo
 */
class UserTest extends RepositoryTestCase
{
    /** @var  m\MockInterface | Repository\Role */
    private $roleRepo;

    /** @var  Repo */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repo::class);

        $this->roleRepo = m::mock();

        /** @var RepositoryServiceManager | m\MockInterface $sm */
        $sm = m::mock(RepositoryServiceManager::class);
        $sm->shouldReceive('get')->once()->with('Role')->andReturn($this->roleRepo);

        $this->sut->initService($sm);
    }

    public function testBuildDefaultQuery(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('u')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(834)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('team', 't')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('contactDetails', 'cd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('cd.person', 'p')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['RESULT']);

        $this->assertSame('RESULT', $this->sut->fetchById(834));
    }

    /**
     * Had to mock SUT as the fetchList method uses Paginator which has proving time consuming to mock
     */
    public function testApplyListFilters(): void
    {
        $sut = m::mock(Repo::class);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $query = \Dvsa\Olcs\Api\Domain\Query\User\UserListSelfserve::create(
            [
                'localAuthority' => 11,
                'partnerContactDetails' => 22,
                'organisation' => 43
            ]
        );

        $mockQb->shouldReceive('andWhere')->with('localAuthority')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('u.localAuthority', ':localAuthority')->once()
            ->andReturn('localAuthority');
        $mockQb->shouldReceive('setParameter')->with('localAuthority', 11)->once();

        $mockQb->shouldReceive('andWhere')->with('partnerContactDetails')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('u.partnerContactDetails', ':partnerContactDetails')->once()
            ->andReturn('partnerContactDetails');
        $mockQb->shouldReceive('setParameter')->with('partnerContactDetails', 22)->once();

        $mockQb->shouldReceive('join')
            ->with('u.organisationUsers', 'ou', \Doctrine\ORM\Query\Expr\Join::WITH, 'ou.organisation = :organisation')
            ->once();
        $mockQb->shouldReceive('setParameter')->with('organisation', 43)->once();

        $mockQb->shouldReceive('expr->neq')->with('u.id', ':systemUser')->once()->andReturn('systemUser');
        $mockQb->shouldReceive('andWhere')->with('systemUser')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('systemUser', IdentityProviderInterface::SYSTEM_USER)->once();

        $sut->applyListFilters($mockQb, $query);
    }

    /**
     * Had to mock SUT as the fetchList method uses Paginator which has proving time consuming to mock
     */
    public function testApplyListFiltersUserList(): void
    {
        $sut = m::mock(Repo::class);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $query = \Dvsa\Olcs\Transfer\Query\User\UserList::create(
            [
                'organisation' => 43,
                'team' => 112,
                'isInternal' => true,
                'roles' => [RoleEntity::ROLE_OPERATOR_USER, RoleEntity::ROLE_OPERATOR_TM]
            ]
        );

        $mockQb->shouldReceive('join')
            ->with('u.organisationUsers', 'ou', \Doctrine\ORM\Query\Expr\Join::WITH, 'ou.organisation = :organisation')
            ->once();
        $mockQb->shouldReceive('setParameter')->with('organisation', 43)->once();

        $mockQb->shouldReceive('andWhere')->with('team')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('u.team', ':team')->once()
            ->andReturn('team');
        $mockQb->shouldReceive('setParameter')->with('team', 112)->once();
        $mockQb->shouldReceive('expr->isNotNull')->with('u.team')->andReturn('isInternal')->once();
        $mockQb->shouldReceive('andWhere')->with('isInternal')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->neq')->with('u.id', ':systemUser')->once()->andReturn('systemUser');
        $mockQb->shouldReceive('andWhere')->with('systemUser')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('systemUser', IdentityProviderInterface::SYSTEM_USER)->once();

        $mockQb->shouldReceive('leftJoin')->with('u.roles', 'r')->once();
        $mockQb->shouldReceive('andWhere')->with('r.role IN (:roles)')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('roles', [RoleEntity::ROLE_OPERATOR_USER, RoleEntity::ROLE_OPERATOR_TM])->once();

        $sut->applyListFilters($mockQb, $query);
    }

    public function testApplyListFiltersUserListExcludeLimitedReadOnly(): void
    {
        $sut = m::mock(Repo::class);

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $trafficAreas = ['A', 'B'];

        $query = UserListInternalByTrafficArea::create(
            [
                'trafficAreas' => $trafficAreas,
                'team' => 112,
                'isInternal' => true,
                'excludeLimitedReadOnly' => true
            ]
        );

        $mockQb->shouldReceive('andWhere')->with('team')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('u.team', ':team')->once()
            ->andReturn('team');
        $mockQb->shouldReceive('setParameter')->with('team', 112)->once();
        $mockQb->shouldReceive('expr->isNotNull')->with('u.team')->andReturn('isInternal')->once();
        $mockQb->shouldReceive('andWhere')->with('isInternal')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->neq')->with('u.id', ':systemUser')->once()->andReturn('systemUser');
        $mockQb->shouldReceive('andWhere')->with('systemUser')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('systemUser', IdentityProviderInterface::SYSTEM_USER)->once();

        $trafficAreaExpr = 'trafficAreaExpr';
        $mockQb->expects('expr->in')
            ->with('t.trafficArea', ':trafficAreas')
            ->andReturn($trafficAreaExpr);
        $mockQb->expects('andWhere')->with($trafficAreaExpr)->andReturnSelf();
        $mockQb->expects('setParameter')->with('trafficAreas', $trafficAreas);

        $em = m::mock(EntityManager::class);
        $mockQb->shouldReceive('getEntityManager')->andReturn($em);

        $subQb = $this->createMockQb('[SUBQUERY]');
        $em->shouldReceive('getRepository->createQueryBuilder')
            ->with('u2')
            ->andReturn($subQb);

        $mockQb->shouldReceive('setParameter')->with('role', RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY)->once();
        $mockQb->shouldReceive('andWhere')->with("u.id NOT IN ");
        $subQb->shouldReceive('getDQL');
        $sut->applyListFilters($mockQb, $query);
    }

    /**
     * Had to mock SUT as the fetchList method uses Paginator which has proving time consuming to mock
     */
    public function testBuildDefaultListQuery(): void
    {
        $sut = m::mock(Repo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $mockQi = m::mock(\Dvsa\Olcs\Transfer\Query\QueryInterface::class);

        $sut->shouldReceive('getQueryBuilder')->with()->andReturn($mockQb);

        $mockQb->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $mockQb->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('team', 't')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('contactDetails', 'cd')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('cd.person', 'p')->once()->andReturnSelf();
        $mockQb->shouldReceive('with')->with('p.disqualifications', 'd')->once()->andReturnSelf();

        $sut->buildDefaultListQuery($mockQb, $mockQi);
    }

    public function testFetchForTma(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('u')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('u.contactDetails', 'cd')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('cd.person', 'cdp')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('u.transportManager', 'tm')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(1)->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getSingleResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchForTma(1));
    }

    public function testFetchByPid(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);

        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('u')->once()->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withPersonContactDetails')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('team')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('organisationUsers')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('roles')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('transportManager')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('localAuthority')->once()->andReturnSelf();

        $mockQb->shouldReceive('where')->with('u.pid = :pid')->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('u.accountDisabled = 0')->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('pid', '123456');
        $mockQb->shouldReceive('getQuery->getOneOrNullResult')->once()->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByPid('123456'));
    }

    public function testPopulateRefDataReference(): void
    {
        $teamId = 1;
        $transportManagerId = 2;
        $partnerContactDetailsId = 3;
        $localAuthorityId = 4;
        $role = 'foo-role';

        $data = [
            'team' => $teamId,
            'transportManager' => $transportManagerId,
            'partnerContactDetails' => $partnerContactDetailsId,
            'localAuthority' => $localAuthorityId,
            'roles' => [$role]
        ];

        $teamEntity = m::mock(TeamEntity::class);
        $this->em->shouldReceive('getReference')
            ->once()
            ->with(TeamEntity::class, $teamId)
            ->andReturn($teamEntity);

        $transportManagerEntity = m::mock(TransportManagerEntity::class);
        $this->em->shouldReceive('getReference')
            ->once()
            ->with(TransportManagerEntity::class, $transportManagerId)
            ->andReturn($transportManagerEntity);

        $partnerContactDetailsEntity = m::mock(ContactDetailsEntity::class);
        $this->em->shouldReceive('getReference')
            ->once()
            ->with(ContactDetailsEntity::class, $partnerContactDetailsId)
            ->andReturn($partnerContactDetailsEntity);

        $localAuthorityEntity = m::mock(LocalAuthorityEntity::class);
        $this->em->shouldReceive('getReference')
            ->once()
            ->with(LocalAuthorityEntity::class, $localAuthorityId)
            ->andReturn($localAuthorityEntity);

        $roleEntity = m::mock(RoleEntity::class);
        $this->roleRepo->shouldReceive('fetchOneByRole')
            ->once()
            ->with($role)
            ->andReturn($roleEntity);

        $result = $this->sut->populateRefDataReference($data);

        $this->assertEquals(
            [
                'team' => $teamEntity,
                'transportManager' => $transportManagerEntity,
                'partnerContactDetails' => $partnerContactDetailsEntity,
                'localAuthority' => $localAuthorityEntity,
                'roles' => [$roleEntity],
            ],
            $result
        );
    }

    public function testFetchForRemindUsername(): void
    {
        $qb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchForRemindUsername('ABC123', 'test@test.me'));

        $expectedQuery = '[QUERY] '
            . 'INNER JOIN u.contactDetails cd AND cd.emailAddress = [[test@test.me]] '
            . 'INNER JOIN u.organisationUsers ou '
            . 'INNER JOIN ou.organisation o '
            . 'INNER JOIN o.licences l AND l.licNo = [[ABC123]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchUsersCountByTeam(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('u')->once()->andReturn($mockQb);

        $mockQb->shouldReceive('select')->with('count(u.id)')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('u.team', ':team')->once()->andReturn('expr');
        $mockQb->shouldReceive('andWhere')->with('expr')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('team', 1)->once()->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getSingleScalarResult')->once()->andReturn('result');

        $this->assertSame('result', $this->sut->fetchUsersCountByTeam(1));
    }

    public function testFindUserNameAvailable(): void
    {
        /** @var Repo | m\MockInterface $sut */
        $sut = m::mock(Repo::class)->makePartial();

        $base = 'unitLogin';

        $sut
            ->shouldReceive('disableSoftDeleteable')->once()
            ->shouldReceive('enableSoftDeleteable')->once()
            ->shouldReceive('fetchByLoginId')->once()->with($base . '3')->andReturn([])
            ->shouldReceive('fetchByLoginId')->andReturn([m::mock(Entity\User\User::class)]);

        $actual = $sut->findUserNameAvailable($base);

        static::assertEquals('unitLogin3', $actual);
    }

    public function testFindUserNameCantGenerateSpecifiedTimes(): void
    {
        /** @var Repo | m\MockInterface $sut */
        $sut = m::mock(Repo::class)->makePartial();

        $sut
            ->shouldReceive('disableSoftDeleteable')->once()
            ->shouldReceive('enableSoftDeleteable')->once()
            ->shouldReceive('fetchByLoginId')
            ->times(10)
            ->andReturn([m::mock(Entity\User\User::class)]);

        static::assertNull($sut->findUserNameAvailable('unitLogin', null, 10));
    }

    public function testFindUserNameCustomSfxGen(): void
    {
        /** @var Repo | m\MockInterface $sut */
        $sut = m::mock(Repo::class)->makePartial();

        $fnc = fn($base, $idx) => $base . '-' . chr(ord('A') + $idx);

        $mockUserE = m::mock(Entity\User\User::class);
        $sut
            ->shouldReceive('disableSoftDeleteable')->once()
            ->shouldReceive('enableSoftDeleteable')->once()
            ->shouldReceive('fetchByLoginId')->times(3)->andReturn([$mockUserE])
            ->shouldReceive('fetchByLoginId')->andReturn([]);

        static::assertEquals(
            'unitLogin-D',
            $sut->findUserNameAvailable('unitLogin', $fnc)
        );
    }

    public function testFetchByLoginId(): void
    {
        $userName = 'unitUserName';

        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn('EXPECT');

        $this->mockCreateQueryBuilder($qb);

        $actual = $this->sut->fetchByLoginId($userName);

        static::assertEquals('QUERY AND u.loginId = [[' . $userName . ']]', $this->query);
        static::assertEquals('EXPECT', $actual);
    }

    public function testFetchUsersCountByRole(): void
    {
        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getSingleScalarResult')->once()->andReturn('EXPECT');

        $this->mockCreateQueryBuilder($qb);

        $actual = $this->sut->fetchUsersCountByRole(RoleEntity::ROLE_SYSTEM_ADMIN);

        static::assertEquals(
            'QUERY '
            . 'SELECT COUNT(DISTINCT u.id) '
            . 'INNER JOIN u.roles r '
            . 'AND r.role = [[' . RoleEntity::ROLE_SYSTEM_ADMIN . ']]',
            $this->query
        );
        static::assertEquals('EXPECT', $actual);
    }
}
