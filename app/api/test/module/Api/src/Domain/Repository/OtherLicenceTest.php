<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\OtherLicence as Repo;
use Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class OtherLicenceTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class
        . ' ol LEFT JOIN ol.role w0 LEFT JOIN ol.previousLicenceType w1';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByTransportManager(): void
    {
        $qb = $this->createRealQb()->willReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchByTransportManager(834));

        $this->assertSame(
            'SELECT ol, w0, w1' . self::FROM . ' WHERE ol.transportManager = :tmId',
            $qb->getDQL(),
        );
        $this->assertSame(834, $qb->getParameter('tmId')->getValue());
    }

    public function testApplyListFilters(): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getTransportManager')->with()->andReturn(33);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(
            'SELECT ol FROM ' . Entity::class . ' ol WHERE ol.transportManager = :tmId',
            $qb->getDQL(),
        );
        $this->assertSame(33, $qb->getParameter('tmId')->getValue());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('parentProvider')]
    public function testFetchForParent(string $method, string $field): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULT']);

        $this->assertSame(['RESULT'], $this->sut->{$method}(1));

        $this->assertSame(
            'SELECT ol, w0, w1' . self::FROM . ' WHERE ol.' . $field . ' = :id',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('id')->getValue());
    }

    public static function parentProvider(): \Iterator
    {
        yield 'application' => ['fetchForTransportManagerApplication', 'transportManagerApplication'];
        yield 'licence' => ['fetchForTransportManagerLicence', 'transportManagerLicence'];
    }
}
