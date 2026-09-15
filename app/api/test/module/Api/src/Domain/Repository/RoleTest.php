<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;

/**
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Role::class)]
final class RoleTest extends RepositoryTestCase
{
    public const string ROLE = 'unit_role';

    /** @var  Repository\Role */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(Repository\Role::class);
    }

    public function testFetchByRole(): void
    {
        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn(['EXPECT']);

        $this->mockCreateQueryBuilder($qb);

        $actual = $this->sut->fetchByRole(self::ROLE);

        $this->assertEquals('QUERY AND m.role = [[' . self::ROLE . ']]', $this->query);
        $this->assertEquals('EXPECT', $actual);
    }

    public function testFetchByRoleNull(): void
    {
        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn([]);

        $this->mockCreateQueryBuilder($qb);

        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Entity\User\Role::class, $this->sut->fetchByRole(self::ROLE));
    }

    public function testFetchOneByRole(): void
    {
        $role = 'foo';

        $qb = $this->createMockQb('QUERY');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery->getSingleResult')->once()->andReturn('foo');

        $result = $this->sut->fetchOneByRole($role);

        $this->assertEquals('QUERY AND m.role = [[foo]]', $this->query);

        $this->assertEquals('foo', $result);
    }
}
