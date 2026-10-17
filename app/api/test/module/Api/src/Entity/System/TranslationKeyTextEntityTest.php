<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\System;

use Dvsa\Olcs\Api\Entity\System\Language;
use Dvsa\Olcs\Api\Entity\System\TranslationKey;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText as Entity;
use Mockery as m;

/**
 * TranslationKeyText Entity Unit Tests
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
final class TranslationKeyTextEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\Override]
    public function getGettersAndSetters(): mixed
    {
        return array_values(array_filter(
            parent::getGettersAndSetters(),
            static fn (array $case): bool => $case[0] !== 'ContentJson',
        ));
    }

    public function testCreateUpdate(): void
    {
        $translationKey = m::mock(TranslationKey::class);
        $language = m::mock(Language::class);
        $translatedText = 'some text for this translation';

        $updatedTranslatedText = 'some updated text for this translation';

        $entity = Entity::create($language, $translationKey, $translatedText);
        $this->assertEquals($language, $entity->getLanguage());
        $this->assertEquals($translationKey, $entity->getTranslationKey());
        $this->assertEquals($translatedText, $entity->getTranslatedText());

        $entity->update($updatedTranslatedText);
        $this->assertEquals($updatedTranslatedText, $entity->getTranslatedText());
        $this->assertEquals($translationKey, $entity->getTranslationKey());
        $this->assertEquals($language, $entity->getLanguage());
    }

    public function testRichSourceCanBeStoredAlongsideRenderedHtml(): void
    {
        $entity = Entity::create(m::mock(Language::class), m::mock(TranslationKey::class), '<p>Before</p>');
        $json = ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'After']]]];

        $this->assertTrue(method_exists($entity, 'getContentJson'));
        $entity->update('<p>After</p>', $json);

        $this->assertSame('<p>After</p>', $entity->getTranslatedText());
        $this->assertSame($json, $entity->getContentJson());
    }
}
