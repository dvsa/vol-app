<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\DiscSequence as Repo;
use Dvsa\Olcs\Api\Entity\System\DiscSequence as Entity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;

final class DiscSequenceTest extends RepositoryTestCase
{
    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * NI is identified by its traffic area alone; everywhere else is "not NI" plus the operator
     * type, because GB goods and PSV discs come from different sequences.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('discPrefixProvider')]
    public function testFetchDiscPrefixes(
        string $niFlag,
        ?string $operatorType,
        string $expectedWhere,
        array $expectedParameters,
    ): void {
        $qb = $this->createRealQb()->willReturn(['result']);

        $this->assertSame(['result'], $this->sut->fetchDiscPrefixes($niFlag, $operatorType));

        $this->assertSame(
            'SELECT ds, ta, gp FROM ' . Entity::class . ' ds'
            . ' LEFT JOIN ds.trafficArea ta LEFT JOIN ds.goodsOrPsv gp'
            . ' WHERE ' . $expectedWhere,
            $qb->getDQL(),
        );

        foreach ($expectedParameters as $name => $expected) {
            $this->assertSame($expected, $qb->getParameter($name)->getValue());
        }
    }

    public static function discPrefixProvider(): \Iterator
    {
        yield 'northern ireland' => [
            'Y',
            null,
            'ta.id IS NOT NULL AND ta.id = :taId',
            ['taId' => TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE],
        ];
        yield 'goods, elsewhere' => [
            'N',
            'lcat_gv',
            'ta.id IS NOT NULL AND ta.id <> :taId AND gp.id = :operatorType',
            [
                'taId' => TrafficAreaEntity::NORTHERN_IRELAND_TRAFFIC_AREA_CODE,
                'operatorType' => 'lcat_gv',
            ],
        ];
    }
}
