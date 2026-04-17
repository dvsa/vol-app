<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Organisation;

use Mockery as m;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName as Entity;

/**
 * TradingName Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TradingNameEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetCalculatedValues(): void
    {
        $organisation = m::mock(Organisation::class);

        $entity = new Entity('Foo', $organisation);
        $data = $entity->jsonSerialize();

        $this->assertSame($organisation, $entity->getOrganisation());
        $this->assertEquals('Foo', $data['name']);
        $this->assertNull($data['organisation']);
        $this->assertNull($data['licence']);
    }
}
