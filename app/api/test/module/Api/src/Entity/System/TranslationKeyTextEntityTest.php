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
class TranslationKeyTextEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

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
}
