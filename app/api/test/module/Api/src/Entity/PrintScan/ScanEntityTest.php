<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\PrintScan;

use DateTime;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\PrintScan\Scan as Entity;
use Mockery as m;

/**
 * Scan Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class ScanEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsBackScan')]
    public function testIsBackScan(mixed $dateReceived, mixed $expected): void
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setDateReceived($dateReceived);

        $this->assertEquals(
            $expected,
            $entity->isBackScan()
        );
    }

    public static function dpIsBackScan(): \Iterator
    {
        yield [null, false];
        yield [new DateTime(), true];
    }
}
