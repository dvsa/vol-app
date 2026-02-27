<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ClearPropertiesWithCollectionsTraitTest
 */
class ClearPropertiesWithCollectionsTraitTest extends MockeryTestCase
{
    public function testClearProperties(): void
    {
        $entity = new StubClearPropertiesWithCollectionsTrait();

        $properties = ['duntExist', 'property'];

        // Test clear properties (Non collection)
        $entity->setProperty('foo');
        $this->assertEquals('foo', $entity->getProperty());
        $entity->clearProperties($properties);
        $this->assertEquals(null, $entity->getProperty());

        // Test clear properties (Collection)
        $collection = new ArrayCollection(['foo', 'bar']);
        $entity->setProperty($collection);
        $this->assertSame($collection, $entity->getProperty());
        $entity->clearProperties($properties);
        $this->assertInstanceOf(ArrayCollection::class, $entity->getProperty());
        $this->assertTrue($entity->getProperty()->isEmpty());
    }
}
