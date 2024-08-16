<?php

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
class ScanEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @dataProvider dpIsBackScan
     */
    public function testIsBackScan($dateReceived, $expected)
    {
        $entity = m::mock(Entity::class)->makePartial();
        $entity->setDateReceived($dateReceived);

        $this->assertEquals(
            $expected,
            $entity->isBackScan()
        );
    }

    public function dpIsBackScan()
    {
        return [
            [null, false],
            [new DateTime(), true],
        ];
    }
}
