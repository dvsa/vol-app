<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Hearing as Repo;
use Dvsa\Olcs\Api\Entity\Cases\Hearing as Entity;
use Mockery as m;

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
        $this->setUpRealSut(Repo::class);
    }

    public function testFetchOneByCase(): void
    {
        $qb = $this->createRealQb()->willReturn('RESULT', 'getSingleResult');

        $this->assertSame('RESULT', $this->sut->fetchOneByCase(123));
        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.case = 123',
            $qb->getDQL(),
        );
    }

    public function testFetchOneByCaseNull(): void
    {
        $this->expectException(NotFoundException::class);

        $this->sut->fetchOneByCase(null);
    }
}
