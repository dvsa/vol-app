<?php

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;

/**
 * @covers \Dvsa\Olcs\Api\Domain\Repository\Role
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RoleTest extends RepositoryTestCase
{
    public const ROLE = 'unit_role';

    /** @var  Repository\Role */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repository\Role::class);
    }

    public function testFetchByRole()
    {
        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn(['EXPECT']);

        $this->mockCreateQueryBuilder($qb);

        $actual = $this->sut->fetchByRole(self::ROLE);

        static::assertEquals('QUERY AND m.role = [[' . self::ROLE . ']]', $this->query);
        static::assertEquals('EXPECT', $actual);
    }

    public function testFetchByRoleNull()
    {
        $qb = $this->createMockQb('QUERY');
        $qb->shouldReceive('getQuery->getResult')->once()->andReturn([]);

        $this->mockCreateQueryBuilder($qb);

        static::assertNull($this->sut->fetchByRole(self::ROLE));
    }

    public function testFetchOneByRole()
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
