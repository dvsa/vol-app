<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\System\RefData as Entity;

/**
 * RefData Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class RefDataEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * Welsh ref-data descriptions are served from ext_translations by the Gedmo
     * TranslatableListener; this mapping was silently lost once before when the
     * entity was regenerated without it.
     */
    public function testDescriptionIsGedmoTranslatable(): void
    {
        $property = new \ReflectionProperty(Entity::class, 'description');

        $this->assertNotEmpty(
            $property->getAttributes(\Gedmo\Mapping\Annotation\Translatable::class),
            'RefData::$description must carry #[Gedmo\Translatable]'
        );
    }
}
