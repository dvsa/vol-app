<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\System\Replacement as Entity;
use Mockery as m;

/**
 * Replacement Entity Unit Tests
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class ReplacementEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testCreateUpdate(): void
    {
        $placeholder = '{{sometext}}';
        $updatedPlaceholder = '{{someextratext}}';

        $replacementText = 'some for creation';
        $updatedReplacementText = 'some updated text for this replacement';

        $entity = Entity::create($placeholder, $replacementText);
        $this->assertEquals($placeholder, $entity->getPlaceholder());
        $this->assertEquals($replacementText, $entity->getReplacementText());

        $entity->update($updatedPlaceholder, $updatedReplacementText);
        $this->assertEquals($updatedPlaceholder, $entity->getPlaceholder());
        $this->assertEquals($updatedReplacementText, $entity->getReplacementText());
    }
}
