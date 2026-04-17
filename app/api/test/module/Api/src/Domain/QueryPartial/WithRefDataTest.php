<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryPartial;

use Doctrine\ORM\EntityManagerInterface;
use Dvsa\Olcs\Api\Domain\QueryPartial\WithRefdata;
use Dvsa\Olcs\Api\Domain\QueryPartial\With;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;

/**
 * WithRefDataTest
 */
class WithRefDataTest extends QueryPartialTestCase
{
    /** @var m\Mock */
    private $em;

    /** @var m\Mock */
    private $with;

    public function setUp(): void
    {
        $this->em = m::mock(EntityManagerInterface::class);
        // Cannot mock With as it is Final
        $this->with = new With();
        $this->sut = new WithRefData($this->em, $this->with);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testModifyQuery(mixed $expectedDql, mixed $arguments, string $entity = 'foo'): void
    {
        $entityMetadata = m::mock();
        $entityMetadata->associationMappings = [
            'property1' => ['targetEntity' => 'Foo'],
            'property2' => ['targetEntity' => RefData::class],
            'property3' => ['targetEntity' => RefData::class],
            'property4' => ['targetEntity' => 'Bar'],
        ];
        $this->em->shouldReceive('getClassMetadata')->with($entity)->once()->andReturn($entityMetadata);
        $this->sut->modifyQuery($this->qb, $arguments);
        $this->assertSame(
            $expectedDql,
            $this->qb->getDQL()
        );
    }

    public static function dataProvider(): array
    {
        return [
            [
                'SELECT a, w0, w1 FROM foo a LEFT JOIN a.property2 w0 LEFT JOIN a.property3 w1',
                []
            ],
            [
                'SELECT a, w0, w1 FROM foo a LEFT JOIN a.property2 w0 LEFT JOIN a.property3 w1',
                ['ENTITY']
            ],
            [
                'SELECT a, w0, w1 FROM foo a LEFT JOIN ALIAS.property2 w0 LEFT JOIN ALIAS.property3 w1',
                ['ENTITY', 'ALIAS'],
                'ENTITY'
            ],
        ];
    }
}
