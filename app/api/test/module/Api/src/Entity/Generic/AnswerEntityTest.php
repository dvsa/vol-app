<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Generic;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Generic\Answer as Entity;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Mockery as m;
use RuntimeException;

/**
 * Answer Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class AnswerEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    #[\Override]
    public function setUp(): void
    {
        $this->entity = $this->instantiate($this->entityClass);
    }

    public function testCreateNewForIrhpApplication(): void
    {
        $questionText = m::mock(QuestionText::class);
        $irhpApplication = m::mock(IrhpApplication::class);

        $entity = $this->entityClass::createNewForIrhpApplication($questionText, $irhpApplication);

        $this->assertInstanceOf($this->entityClass, $entity);
        $this->assertSame($questionText, $entity->getQuestionText());
        $this->assertSame($irhpApplication, $entity->getIrhpApplication());
        $this->assertNull($entity->getIrhpPermitApplication());
    }

    public function testCreateNewForIrhpPermitApplication(): void
    {
        $questionText = m::mock(QuestionText::class);
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $entity = $this->entityClass::createNewForIrhpPermitApplication($questionText, $irhpPermitApplication);

        $this->assertInstanceOf($this->entityClass, $entity);
        $this->assertSame($questionText, $entity->getQuestionText());
        $this->assertNull($entity->getIrhpApplication());
        $this->assertSame($irhpPermitApplication, $entity->getIrhpPermitApplication());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpValueGetterAndSetter')]
    public function testValueGetterAndSetter(mixed $questionType, mixed $answerValue): void
    {
        $this->assertNull($this->entity->getValue());
        $this->entity->setValue($questionType, $answerValue);

        $this->assertEquals($answerValue, $this->entity->getValue());
    }

    public static function dpValueGetterAndSetter(): \Iterator
    {
        // string
        yield [Question::QUESTION_TYPE_STRING, 'abc'];
        yield [Question::QUESTION_TYPE_STRING, ''];
        yield [Question::QUESTION_TYPE_STRING, 123];
        yield [Question::QUESTION_TYPE_STRING, 0];
        yield [Question::QUESTION_TYPE_STRING, true];
        yield [Question::QUESTION_TYPE_STRING, false];
        yield [Question::QUESTION_TYPE_STRING, 'true'];
        yield [Question::QUESTION_TYPE_STRING, 'false'];
        // int
        yield [Question::QUESTION_TYPE_INTEGER, 'abc'];
        yield [Question::QUESTION_TYPE_INTEGER, ''];
        yield [Question::QUESTION_TYPE_INTEGER, 123];
        yield [Question::QUESTION_TYPE_INTEGER, 0];
        yield [Question::QUESTION_TYPE_INTEGER, true];
        yield [Question::QUESTION_TYPE_INTEGER, false];
        yield [Question::QUESTION_TYPE_INTEGER, 'true'];
        yield [Question::QUESTION_TYPE_INTEGER, 'false'];
        // bool
        yield [Question::QUESTION_TYPE_BOOLEAN, 'abc'];
        yield [Question::QUESTION_TYPE_BOOLEAN, ''];
        yield [Question::QUESTION_TYPE_BOOLEAN, 123];
        yield [Question::QUESTION_TYPE_BOOLEAN, 0];
        yield [Question::QUESTION_TYPE_BOOLEAN, true];
        yield [Question::QUESTION_TYPE_BOOLEAN, false];
        yield [Question::QUESTION_TYPE_BOOLEAN, 'true'];
        yield [Question::QUESTION_TYPE_BOOLEAN, 'false'];
    }

    public function testSetValueForCustomType(): void
    {
        $this->expectException(RuntimeException::class);

        $this->entity->setValue(Question::QUESTION_TYPE_CUSTOM, 'custom');
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsEqualTo')]
    public function testIsEqualTo(mixed $questionType, mixed $answerValue, mixed $checkValue, mixed $expected): void
    {
        $this->assertNull($this->entity->getValue());

        $this->entity->setValue($questionType, $answerValue);

        $this->assertEquals($expected, $this->entity->isEqualTo($checkValue));
    }

    public static function dpIsEqualTo(): \Iterator
    {
        // matching values
        // string
        yield [Question::QUESTION_TYPE_STRING, 'abc', 'abc', true];
        yield [Question::QUESTION_TYPE_STRING, 'abc', true, true];
        yield [Question::QUESTION_TYPE_STRING, '', '', true];
        yield [Question::QUESTION_TYPE_STRING, 'false', true, true];
        yield [Question::QUESTION_TYPE_STRING, '', 0, true];
        yield [Question::QUESTION_TYPE_STRING, '', false, true];
        yield [Question::QUESTION_TYPE_STRING, '0', 0, true];
        yield [Question::QUESTION_TYPE_STRING, '0', false, true];
        // int
        yield [Question::QUESTION_TYPE_INTEGER, 123, 123, true];
        yield [Question::QUESTION_TYPE_INTEGER, 123, '123', true];
        yield [Question::QUESTION_TYPE_INTEGER, 0, 0, true];
        yield [Question::QUESTION_TYPE_INTEGER, 0, '0', true];
        yield [Question::QUESTION_TYPE_INTEGER, 0, false, true];
        yield [Question::QUESTION_TYPE_INTEGER, 1, true, true];
        yield [Question::QUESTION_TYPE_INTEGER, 123, true, true];
        // bool
        yield [Question::QUESTION_TYPE_BOOLEAN, true, true, true];
        yield [Question::QUESTION_TYPE_BOOLEAN, true, 1, true];
        yield [Question::QUESTION_TYPE_BOOLEAN, true, '1', true];
        yield [Question::QUESTION_TYPE_BOOLEAN, true, 'false', true];
        yield [Question::QUESTION_TYPE_BOOLEAN, true, 'abc', true];
        yield [Question::QUESTION_TYPE_BOOLEAN, false, false, true];
        yield [Question::QUESTION_TYPE_BOOLEAN, false, 0, true];
        yield [Question::QUESTION_TYPE_BOOLEAN, false, '0', true];
        // different values
        // string
        yield [Question::QUESTION_TYPE_STRING, 'abc', 'def', false];
        yield [Question::QUESTION_TYPE_STRING, '', 'def', false];
        yield [Question::QUESTION_TYPE_STRING, 'false', false, false];
        // int
        yield [Question::QUESTION_TYPE_INTEGER, 123, 987, false];
        // bool
        yield [Question::QUESTION_TYPE_BOOLEAN, true, false, false];
        yield [Question::QUESTION_TYPE_BOOLEAN, false, true, false];
    }

    public function testIsEqualToWhenValueNotSet(): void
    {
        $this->expectException(RuntimeException::class);

        $this->entity->isEqualTo('anything');
    }
}
