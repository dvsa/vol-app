<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\AnswerSaver;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\Answer as AnswerRepository;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Entity\Generic\QuestionText;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\AnswerFactory;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerProvider;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * GenericAnswerWriterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class GenericAnswerWriterTest extends MockeryTestCase
{
    /**
     * @var string
     */
    public $questionType;
    private $questionId;

    private $qaEntityId;

    private $answerValue;

    private $answer;

    private $answerRepo;

    private $answerFactory;

    private $question;

    private $applicationStep;

    private $qaEntity;

    private $qaContext;

    private $genericAnswerProvider;

    private $genericAnswerWriter;

    public function setUp(): void
    {
        $this->questionId = 43;

        $this->questionType = Question::QUESTION_TYPE_STRING;

        $this->qaEntityId = 47;

        $this->answerValue = 866;

        $this->answer = m::mock(Answer::class);

        $this->answerRepo = m::mock(AnswerRepository::class);

        $this->answerFactory = m::mock(AnswerFactory::class);

        $this->question = m::mock(Question::class);

        $this->applicationStep = m::mock(ApplicationStep::class);
        $this->applicationStep->shouldReceive('getQuestion')
            ->withNoArgs()
            ->andReturn($this->question);

        $this->qaEntity = m::mock(QaEntityInterface::class);
        $this->qaEntity->shouldReceive('addAnswers')
            ->with($this->answer)
            ->once();

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($this->applicationStep);
        $this->qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($this->qaEntity);

        $this->genericAnswerProvider = m::mock(GenericAnswerProvider::class);

        $this->genericAnswerWriter = new GenericAnswerWriter(
            $this->genericAnswerProvider,
            $this->answerFactory,
            $this->answerRepo
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpForceQuestionType')]
    public function testSaveAnswerAlreadyExists(mixed $questionType, mixed $forcedType, mixed $expectedType): void
    {
        $this->answer->shouldReceive('setValue')
            ->with($expectedType, $this->answerValue)
            ->once()
            ->globally()
            ->ordered();
        $this->answerRepo->shouldReceive('save')
            ->with($this->answer)
            ->once()
            ->globally()
            ->ordered();

        $this->question->shouldReceive('getQuestionType')
            ->andReturn($questionType);

        $this->genericAnswerProvider->shouldReceive('get')
            ->with($this->qaContext)
            ->andReturn($this->answer);

        $this->genericAnswerWriter->write(
            $this->qaContext,
            $this->answerValue,
            $forcedType
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpForceQuestionType')]
    public function testSaveAnswerRequiresCreation(mixed $questionType, mixed $forcedType, mixed $expectedType): void
    {
        $questionText = m::mock(QuestionText::class);

        $this->question->shouldReceive('getActiveQuestionText')
            ->andReturn($questionText);
        $this->question->shouldReceive('getQuestionType')
            ->andReturn($questionType);

        $this->genericAnswerProvider->shouldReceive('get')
            ->with($this->qaContext)
            ->andThrow(new NotFoundException());

        $this->answer->shouldReceive('setValue')
            ->with($expectedType, $this->answerValue)
            ->once()
            ->globally()
            ->ordered();

        $this->answerRepo->shouldReceive('save')
            ->with($this->answer)
            ->once()
            ->globally()
            ->ordered();

        $this->answerFactory->shouldReceive('create')
            ->with($questionText, $this->qaEntity)
            ->andReturn($this->answer);

        $this->genericAnswerWriter->write(
            $this->qaContext,
            $this->answerValue,
            $forcedType
        );
    }

    public static function dpForceQuestionType(): array
    {
        return [
            [Question::QUESTION_TYPE_INTEGER, null, Question::QUESTION_TYPE_INTEGER],
            [Question::QUESTION_TYPE_STRING, null, Question::QUESTION_TYPE_STRING],
            [Question::QUESTION_TYPE_STRING, Question::QUESTION_TYPE_INTEGER, Question::QUESTION_TYPE_INTEGER],
            [Question::QUESTION_TYPE_STRING, Question::QUESTION_TYPE_BOOLEAN, Question::QUESTION_TYPE_BOOLEAN],
        ];
    }
}
