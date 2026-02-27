<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ClearPropertiesTraitTest
 */
class ClearPropertiesTraitTest extends MockeryTestCase
{
    public function testClearProperties(): void
    {
        $entity = new StubClearPropertiesTrait();

        $properties = ['duntExist', 'property'];

        // Test clear properties (Non collection)
        $entity->setProperty('foo');
        $this->assertEquals('foo', $entity->getProperty());
        $entity->clearProperties($properties);
        $this->assertEquals(null, $entity->getProperty());
    }
}
