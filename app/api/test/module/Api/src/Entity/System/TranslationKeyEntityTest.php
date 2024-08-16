<?php

namespace Dvsa\OlcsTest\Api\Entity\System;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\System\TranslationKey as Entity;
use Mockery as m;

/**
 * TranslationKey Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TranslationKeyEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    /**
     * @dataProvider canDeleteProvider
     */
    public function testCanDelete($transKeyTexts, $expected)
    {
        $entity = Entity::create(
            'id',
            'description'
        );
        $entity->addTranslationKeyTexts($transKeyTexts);
        $this->assertEquals($expected, $entity->canDelete());
    }

    public function canDeleteProvider()
    {
        $noTexts = new ArrayCollection();
        $texts = new ArrayCollection([m::mock(TranslationKeyText::class)]);
        return [
            [$noTexts, true],
            [$texts, false]
        ];
    }

    /**
     * Test create
     */
    public function testCreate()
    {
        $entity = Entity::create('transKey', 'description');

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('transKey', $entity->getTranslationKey());
        $this->assertEquals('description', $entity->getDescription());
    }
}
