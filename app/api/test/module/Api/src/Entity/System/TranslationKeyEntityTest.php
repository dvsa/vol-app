<?php

declare(strict_types=1);

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
final class TranslationKeyEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\PHPUnit\Framework\Attributes\DataProvider('canDeleteProvider')]
    public function testCanDelete(\Closure $createTransKeyTexts, mixed $expected): void
    {
        $entity = Entity::create(
            'id',
            'description'
        );
        $entity->addTranslationKeyTexts($createTransKeyTexts());
        $this->assertEquals($expected, $entity->canDelete());
    }

    public static function canDeleteProvider(): \Iterator
    {
        yield [static fn () => new ArrayCollection(), true];
        yield [static fn () => new ArrayCollection([m::mock(TranslationKeyText::class)]), false];
    }

    /**
     * Test create
     */
    public function testCreate(): void
    {
        $entity = Entity::create('transKey', 'description');

        $this->assertInstanceOf(Entity::class, $entity);
        $this->assertEquals('transKey', $entity->getTranslationKey());
        $this->assertEquals('description', $entity->getDescription());
    }
}
