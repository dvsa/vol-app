<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\TrafficArea as TrafficAreaRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as Entity;
use Mockery as m;

final class TrafficAreaTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(TrafficAreaRepo::class);
    }

    /**
     * Every location but NI restricts to isNi = 0; NI itself is unrestricted.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('valueOptionsProvider')]
    public function testGetValueOptions(?string $allowedOperatorLocation, string $expectedWhere): void
    {
        $qb = $this->createRealQb()->willReturn([
            $this->trafficArea('A', 'Area A'),
            $this->trafficArea('B', 'Area B'),
        ]);

        $this->assertSame(
            ['A' => 'Area A', 'B' => 'Area B'],
            $this->sut->getValueOptions(...($allowedOperatorLocation === null ? [] : [$allowedOperatorLocation])),
        );

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m' . $expectedWhere . ' ORDER BY m.name ASC',
            $qb->getDQL(),
        );
    }

    public static function valueOptionsProvider(): \Iterator
    {
        yield 'unspecified' => [null, ' WHERE m.isNi = :isNi'];
        yield 'GB' => [Organisation::ALLOWED_OPERATOR_LOCATION_GB, ' WHERE m.isNi = :isNi'];
        yield 'NI' => [Organisation::ALLOWED_OPERATOR_LOCATION_NI, ''];
    }

    public function testFetchListForNewApplication(): void
    {
        $qb = $this->createRealQb()->willReturn('results');

        $this->assertSame('results', $this->sut->fetchListForNewApplication('GB'));

        $this->assertSame(
            'SELECT m FROM ' . Entity::class . ' m WHERE m.isNi = :isNi',
            $qb->getDQL(),
        );
        $this->assertSame('0', $qb->getParameter('isNi')->getValue());
    }

    private function trafficArea(string $id, string $name): m\MockInterface
    {
        $ta = m::mock(Entity::class);
        $ta->shouldReceive('getId')->andReturn($id);
        $ta->shouldReceive('getName')->andReturn($name);

        return $ta;
    }
}
