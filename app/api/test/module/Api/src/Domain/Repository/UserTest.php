<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Query\User\UserListInternalByTrafficArea;
use Dvsa\Olcs\Api\Domain\Query\User\UserListSelfserve;
use Dvsa\Olcs\Api\Domain\Repository\User as Repo;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Rbac\IdentityProviderInterface;
use Dvsa\Olcs\Api\Entity\User\Role as RoleEntity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query\User\UserList;
use Mockery as m;

final class UserTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . UserEntity::class . ' u';

    /** Applied to every list: the system user is never shown. */
    private const string SYSTEM_USER_WHERE = 'u.id <> :systemUser';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testBuildDefaultQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->buildDefaultQuery($qb, 1);

        $this->assertSame(
            'SELECT u, t, cd, p' . self::FROM
            . ' LEFT JOIN u.team t LEFT JOIN u.contactDetails cd LEFT JOIN cd.person p'
            . ' WHERE u.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testBuildDefaultListQuery(): void
    {
        $qb = $this->createRealQb();

        $this->sut->buildDefaultListQuery($qb, m::mock(QueryInterface::class));

        $this->assertSame(
            'SELECT u, t, cd, p, d' . self::FROM
            . ' LEFT JOIN u.team t LEFT JOIN u.contactDetails cd LEFT JOIN cd.person p'
            . ' LEFT JOIN p.disqualifications d',
            $qb->getDQL(),
        );
    }

    /**
     * Which filters apply is decided by the query class, since each is gated on method_exists().
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(string $queryClass, array $data, string $expectedTail): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, $queryClass::create($data));

        $this->assertSame('SELECT u' . self::FROM . $expectedTail, $qb->getDQL());
        $this->assertSame(
            IdentityProviderInterface::SYSTEM_USER,
            $qb->getParameter('systemUser')->getValue(),
        );
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'selfserve list' => [
            UserListSelfserve::class,
            [
                'localAuthority' => 11,
                'partnerContactDetails' => 22,
                'organisation' => 43,
                'lastLoggedInFrom' => '2015-01-01',
            ],
            // The organisation filter is a join condition, not a WHERE clause.
            ' INNER JOIN u.organisationUsers ou WITH ou.organisation = :organisation'
            . ' WHERE u.localAuthority = :localAuthority'
            . ' AND u.partnerContactDetails = :partnerContactDetails'
            . ' AND u.lastLoginAt >= :lastLoggedInFrom'
            . ' AND ' . self::SYSTEM_USER_WHERE,
        ];

        yield 'internal user list' => [
            UserList::class,
            [
                'organisation' => 43,
                'team' => 112,
                'isInternal' => true,
                'roles' => [RoleEntity::ROLE_OPERATOR_USER, RoleEntity::ROLE_OPERATOR_TM],
            ],
            ' INNER JOIN u.organisationUsers ou WITH ou.organisation = :organisation'
            . ' LEFT JOIN u.roles r'
            . ' WHERE u.team = :team AND u.team IS NOT NULL AND r.role IN (:roles)'
            . ' AND ' . self::SYSTEM_USER_WHERE,
        ];
    }

    /**
     * Limited read-only users are excluded with a NOT IN over a correlated sub-select rather
     * than a join, so the exclusion cannot multiply rows.
     */
    public function testApplyListFiltersExcludingLimitedReadOnly(): void
    {
        $qb = $this->createRealQb();

        $query = UserListInternalByTrafficArea::create([
            'trafficAreas' => ['B'],
            'team' => 112,
            'isInternal' => true,
            'excludeLimitedReadOnly' => true,
        ]);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT u' . self::FROM
            . ' WHERE u.team = :team AND t.trafficArea IN(:trafficAreas) AND u.team IS NOT NULL'
            . ' AND u.id NOT IN(SELECT u2.id FROM ' . UserEntity::class . ' u2'
            . ' LEFT JOIN u2.roles r WHERE r.role = :role)'
            . ' AND ' . self::SYSTEM_USER_WHERE,
            $qb->getDQL(),
        );
        $this->assertSame(
            RoleEntity::ROLE_INTERNAL_LIMITED_READ_ONLY,
            $qb->getParameter('role')->getValue(),
        );
        $this->assertSame(['B'], $qb->getParameter('trafficAreas')->getValue());
    }

    public function testFetchForTma(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchForTma(1));

        $this->assertSame(
            'SELECT u, cd, cdp, tm' . self::FROM
            . ' LEFT JOIN u.contactDetails cd LEFT JOIN cd.person cdp'
            . ' LEFT JOIN u.transportManager tm'
            . ' WHERE u.id = :byId',
            $qb->getDQL(),
        );
    }

    /**
     * Both identity lookups build the same query, differing only in the column matched.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('identityProvider')]
    public function testFetchIdentity(string $method, mixed $value, string $column, string $parameter): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getOneOrNullResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->{$method}($value));

        $this->assertSame(
            'SELECT u, c, p, a, ct, pc, w0, w1, w2, w3, w4' . self::FROM
            . ' LEFT JOIN u.contactDetails c LEFT JOIN c.person p LEFT JOIN c.address a'
            . ' LEFT JOIN c.contactType ct LEFT JOIN c.phoneContacts pc'
            . ' LEFT JOIN u.team w0 LEFT JOIN u.organisationUsers w1 LEFT JOIN u.roles w2'
            . ' LEFT JOIN u.transportManager w3 LEFT JOIN u.localAuthority w4'
            . ' WHERE u.' . $column . ' = :' . $parameter . ' AND u.accountDisabled = 0',
            $qb->getDQL(),
        );
        $this->assertSame($value, $qb->getParameter($parameter)->getValue());
    }

    public static function identityProvider(): \Iterator
    {
        yield 'by pid' => ['fetchByPid', 'PID', 'pid', 'pid'];
        yield 'by login id' => ['fetchEnabledIdentityByLoginId', 'login', 'loginId', 'loginId'];
    }

    public function testFetchForRemindUsername(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForRemindUsername('ABC123', 'test@test.me'));

        // Matched on email and licence number together, so both must belong to the same user.
        $this->assertSame(
            'SELECT u' . self::FROM
            . ' INNER JOIN u.contactDetails cd INNER JOIN u.organisationUsers ou'
            . ' INNER JOIN ou.organisation o INNER JOIN o.licences l'
            . ' WHERE cd.emailAddress = :emailAddress AND l.licNo = :licNo',
            $qb->getDQL(),
        );
        $this->assertSame('test@test.me', $qb->getParameter('emailAddress')->getValue());
        $this->assertSame('ABC123', $qb->getParameter('licNo')->getValue());
    }

    public function testFetchFirstByEmailOrFalse(): void
    {
        $user = m::mock(UserEntity::class);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs();
        $qb->stubbedQuery()->expects('getResult')->withNoArgs()->andReturn([$user]);

        $this->assertSame($user, $this->sut->fetchFirstByEmailOrFalse('test@test.me'));

        $this->assertSame(
            'SELECT u' . self::FROM . ' INNER JOIN u.contactDetails cd'
            . ' WHERE cd.emailAddress = :emailAddress',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getMaxResults());
    }

    public function testFetchByLoginId(): void
    {
        $qb = $this->createRealQb()->willReturn('EXPECT');

        $this->assertSame('EXPECT', $this->sut->fetchByLoginId('unitLogin'));

        $this->assertSame(
            'SELECT u' . self::FROM . ' WHERE u.loginId = :loginId',
            $qb->getDQL(),
        );
        $this->assertSame('unitLogin', $qb->getParameter('loginId')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('countProvider')]
    public function testUserCounts(string $method, mixed $arg, string $expectedDql, string $parameter): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleScalarResult')->andReturn('result');

        $this->assertSame('result', $this->sut->{$method}($arg));

        $this->assertSame($expectedDql, $qb->getDQL());
        $this->assertSame($arg, $qb->getParameter($parameter)->getValue());
    }

    public static function countProvider(): \Iterator
    {
        yield 'by team' => [
            'fetchUsersCountByTeam',
            1,
            'SELECT count(u.id)' . self::FROM . ' WHERE u.team = :team',
            'team',
        ];
        // DISTINCT because a user can hold the role more than once through separate rows.
        yield 'by role' => [
            'fetchUsersCountByRole',
            'role',
            'SELECT COUNT(DISTINCT u.id)' . self::FROM . ' INNER JOIN u.roles r'
            . ' WHERE r.role = :role',
            'role',
        ];
    }

    /**
     * The generator keeps appending a suffix until a login id is free, then stops.
     */
    public function testFindUserNameAvailable(): void
    {
        $this->expectSoftDeleteableToggled();

        $this->sut->shouldReceive('fetchByLoginId')->with('unitLogin3')->andReturn([])
            ->shouldReceive('fetchByLoginId')->andReturn([m::mock(UserEntity::class)]);

        $this->assertSame('unitLogin3', $this->sut->findUserNameAvailable('unitLogin'));
    }

    public function testFindUserNameAvailableGivesUpAfterTheTryCount(): void
    {
        $this->expectSoftDeleteableToggled();

        $this->sut->shouldReceive('fetchByLoginId')->andReturn([m::mock(UserEntity::class)]);

        $this->assertNull($this->sut->findUserNameAvailable('unitLogin', null, 10));
    }

    public function testFindUserNameAvailableWithACustomSuffixGenerator(): void
    {
        $this->expectSoftDeleteableToggled();

        $this->sut->shouldReceive('fetchByLoginId')->times(3)->andReturn([m::mock(UserEntity::class)])
            ->shouldReceive('fetchByLoginId')->andReturn([]);

        $suffix = fn($base, $idx) => $base . '-' . chr(65 + $idx);

        $this->assertSame('unitLogin-D', $this->sut->findUserNameAvailable('unitLogin', $suffix));
    }

    public function testPopulateRefDataReference(): void
    {
        $team = m::mock(Entity\User\Team::class);
        $role = m::mock(RoleEntity::class);

        $this->em->shouldReceive('getReference')->andReturn($team);

        $roleRepo = m::mock();
        $roleRepo->shouldReceive('fetchOneByRole')->andReturn($role);

        $serviceManager = m::mock(\Dvsa\Olcs\Api\Domain\RepositoryServiceManager::class);
        $serviceManager->shouldReceive('get')->with('Role')->andReturn($roleRepo);
        $this->sut->initService($serviceManager);

        $this->assertSame(
            ['team' => $team, 'roles' => [$role]],
            $this->sut->populateRefDataReference(['team' => 1, 'roles' => ['r1']]),
        );
    }

    private function expectSoftDeleteableToggled(): void
    {
        $filters = m::mock(\Doctrine\ORM\Query\FilterCollection::class);
        $filters->shouldReceive('isEnabled')->with('soft-deleteable')->andReturnTrue();
        $filters->shouldReceive('disable')->with('soft-deleteable');
        $filters->shouldReceive('enable')->with('soft-deleteable');

        $this->em->shouldReceive('getFilters')->withNoArgs()->andReturn($filters);
    }
}
