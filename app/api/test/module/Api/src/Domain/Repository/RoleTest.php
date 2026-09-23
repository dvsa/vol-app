<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\User\Role as Entity;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Role::class)]
final class RoleTest extends RepositoryTestCase
{
    public const string ROLE = 'unit_role';

    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repository\Role::class);
    }

    public function testFetchByRole(): void
    {
        $qb = $this->createRealQb()->willReturn(['EXPECT']);

        $this->assertSame('EXPECT', $this->sut->fetchByRole(self::ROLE));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.role = :role',
            $qb->getDQL(),
        );
        $this->assertSame(self::ROLE, $qb->getParameter('role')->getValue());
    }

    public function testFetchByRoleNull(): void
    {
        $this->createRealQb()->willReturn([]);

        $this->assertNull($this->sut->fetchByRole(self::ROLE));
    }

    public function testFetchOneByRole(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('foo');

        $this->assertSame('foo', $this->sut->fetchOneByRole('foo'));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.role = :role',
            $qb->getDQL(),
        );
        $this->assertSame('foo', $qb->getParameter('role')->getValue());
    }
}
