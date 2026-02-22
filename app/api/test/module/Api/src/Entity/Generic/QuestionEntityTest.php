<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Generic;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Generic\Answer;
use Dvsa\Olcs\Api\Entity\Generic\Question as Entity;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText as QuestionTextEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Mockery as m;

/**
 * Question Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class QuestionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function setUp(): void
    {
        $this->entity = $this->instantiate($this->entityClass);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsCustom')]
    public function testIsCustom(mixed $id, mixed $expected): void
    {
        $questionType = new RefData($id);

        $this->entity->setQuestionType($questionType);

        $this->assertEquals($expected, $this->entity->isCustom());
    }

    public static function dpIsCustom(): array
    {
        return [
            [Entity::QUESTION_TYPE_STRING, false],
            [Entity::QUESTION_TYPE_INTEGER, false],
            [Entity::QUESTION_TYPE_BOOLEAN, false],
            [Entity::QUESTION_TYPE_CUSTOM, true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetActiveQuestionText')]
    public function testGetActiveQuestionText(mixed $questionTexts, mixed $dateToCheck, mixed $expected): void
    {
        $this->entity->setQuestionTexts($questionTexts);

        $this->assertEquals($expected, $this->entity->getActiveQuestionText($dateToCheck));
    }

    public static function dpGetActiveQuestionText(): array
    {
        $inPast = new DateTime('last year');
        $lastWeek = new DateTime('-1 week');
        $yesterday = new DateTime('yesterday');
        $inFuture = new DateTime('next year');

        $questionTextInPast = new QuestionTextEntity();
        $questionTextInPast->setEffectiveFrom($inPast);

        $questionTextYesterday = new QuestionTextEntity();
        $questionTextYesterday->setEffectiveFrom($yesterday);

        $questionTextInFuture = new QuestionTextEntity();
        $questionTextInFuture->setEffectiveFrom($inFuture);

        return [
            'only one app path - in the past' => [
                'questionTexts' => new ArrayCollection([$questionTextInPast]),
                'dateToCheck' => null,
                'expected' => $questionTextInPast,
            ],
            'two app paths - both in the past' => [
                'questionTexts' => new ArrayCollection([$questionTextInPast, $questionTextYesterday]),
                'dateToCheck' => null,
                'expected' => $questionTextYesterday,
            ],
            'two app paths - both in the past - check against yesterdays date' => [
                'questionTexts' => new ArrayCollection([$questionTextInPast, $questionTextYesterday]),
                'dateToCheck' => $yesterday,
                'expected' => $questionTextYesterday,
            ],
            'two app paths - both in the past - check against last weeks date' => [
                'questionTexts' => new ArrayCollection([$questionTextInPast, $questionTextYesterday]),
                'dateToCheck' => $lastWeek,
                'expected' => $questionTextInPast,
            ],
            'three app paths - two in the past and one in the future' => [
                'questionTexts' => new ArrayCollection(
                    [$questionTextInPast, $questionTextYesterday, $questionTextInFuture]
                ),
                'dateToCheck' => null,
                'expected' => $questionTextYesterday,
            ],
            'only one app path - in the future' => [
                'questionTexts' => new ArrayCollection([$questionTextInFuture]),
                'dateToCheck' => null,
                'expected' => null,
            ],
        ];
    }

    public function testGetDecodedOptionSource(): void
    {
        $optionSourceAsJson = '{"option1": "value1", "option2": "value2"}';

        $optionSourceAsArray = [
            'option1' => 'value1',
            'option2' => 'value2'
        ];

        $entity = m::mock(Entity::class)->makePartial();
        $entity->setOptionSource($optionSourceAsJson);

        $this->assertEquals(
            $optionSourceAsArray,
            $entity->getDecodedOptionSource()
        );
    }

    public function testGetStandardAnswer(): void
    {
        $activeQuestionTextId = 77;

        $answerValue = 'foo';

        $answer1 = m::mock(Answer::class);
        $answer1->shouldReceive('getQuestionText->getId')
            ->withNoArgs()
            ->andReturn(70);

        $answer2 = m::mock(Answer::class);
        $answer2->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn($answerValue);
        $answer2->shouldReceive('getQuestionText->getId')
            ->withNoArgs()
            ->andReturn($activeQuestionTextId);

        $answer3 = m::mock(Answer::class);
        $answer3->shouldReceive('getQuestionText->getId')
            ->withNoArgs()
            ->andReturn(80);

        $answersArrayCollection = new ArrayCollection([$answer1, $answer2, $answer3]);

        $qaEntity = m::mock(QaEntityInterface::class);
        $qaEntity->shouldReceive('getAnswers')
            ->withNoArgs()
            ->andReturn($answersArrayCollection);

        $applicationPathLockedOn = m::mock(\DateTime::class);

        $activeQuestionText = m::mock(QuestionTextEntity::class);
        $activeQuestionText->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($activeQuestionTextId);

        $entity = m::mock(Entity::class)->makePartial();
        $entity->shouldReceive('getActiveQuestionText')
            ->with($applicationPathLockedOn)
            ->andReturn($activeQuestionText);

        $this->assertEquals(
            $answerValue,
            $entity->getStandardAnswer($qaEntity, $applicationPathLockedOn)
        );
    }
}
