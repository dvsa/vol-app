<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Generic;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText as Entity;

/**
 * QuestionText Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class QuestionTextEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testGetTranslationKeyFromQuestionKey(): void
    {
        $questionKey = '{
            "filter": "htmlEscape",
            "translateableText": {
                "key": "qanda.certificate-of-roadworthiness.trailer.vehicle-identification-number.question"
            }
        }';

        $questionText = new Entity();
        $questionText->setQuestionKey($questionKey);

        $expectedTranslationKey = 'qanda.certificate-of-roadworthiness.trailer.vehicle-identification-number.question';

        $this->assertEquals(
            $expectedTranslationKey,
            $questionText->getTranslationKeyFromQuestionKey()
        );
    }
}
