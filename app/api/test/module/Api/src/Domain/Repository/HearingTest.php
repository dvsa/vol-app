<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Hearing as Repo;

/**
 * HearingTest
 *
 */
final class HearingTest extends RepositoryTestCase
{
    /** @var m\MockInterface|Repo */
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchOneByCase(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getSingleResult')
                ->andReturn('RESULT')
                ->getMock()
        );
        $this->assertEquals('RESULT', $this->sut->fetchOneByCase(123));

        $expectedQuery = 'BLAH AND m.case = 123';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchOneByCaseNull(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->expectException(NotFoundException::class);
        $this->sut->fetchOneByCase(null);
    }
}
