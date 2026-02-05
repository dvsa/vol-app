<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationUser as Repo;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\OrganisationUser::class)]
class OrganisationUserTest extends RepositoryTestCase
{
    /**
     * @var Repo
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repo::class, true);
    }

    public function testFetchByUserId(): void
    {
        $userId = 1;

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                    ->shouldReceive('execute')
                    ->once()
                    ->shouldReceive('getResult')
                    ->andReturn(['res'])
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('expr')
            ->andReturn(
                m::mock()
                    ->shouldReceive('eq')
                    ->with('m.user', $userId)
                    ->andReturn('wherecond')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('andWhere')
            ->with('wherecond')
            ->once()
            ->getMock();

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->once()
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(OrganisationUser::class)
            ->once()
            ->andReturn($repo);

        $this->assertEquals(['res'], $this->sut->fetchByUserId($userId));
    }

    public function testDeleteByUserId(): void
    {
        $userId = 1;

        $this->sut->shouldReceive('fetchByUserId')
            ->with($userId)
            ->once()
            ->andReturn(['FOO'])
            ->shouldReceive('delete')
            ->with('FOO')
            ->once();

        $this->assertNull($this->sut->deleteByUserId($userId));
    }
}
