<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep;
use Dvsa\Olcs\Api\Entity\Generic\Question;
use Dvsa\Olcs\Api\Service\Qa\AnswerSaver\GenericAnswerWriter;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\BaseAnswerSaver;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\GenericAnswerFetcher;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * BaseAnswerSaverTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class BaseAnswerSaverTest extends MockeryTestCase
{
    private $qaElementValue = 'qaElementValue';

    private $postData;

    private $qaContext;

    private $genericAnswerWriter;

    private $baseAnswerSaver;

    #[\Override]
    public function setUp(): void
    {
        $fieldsetName = 'fields456';

        $this->postData = [
            $fieldsetName => [
                'qaElement' => 'qaElementValue'
            ]
        ];

        $applicationStep = m::mock(ApplicationStep::class);

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($applicationStep);

        $this->genericAnswerWriter = m::mock(GenericAnswerWriter::class);

        $genericAnswerFetcher = m::mock(GenericAnswerFetcher::class);
        $genericAnswerFetcher->shouldReceive('fetch')
            ->with($applicationStep, $this->postData)
            ->andReturn($this->qaElementValue);

        $this->baseAnswerSaver = new BaseAnswerSaver($this->genericAnswerWriter, $genericAnswerFetcher);
    }

    public function testSaveWithoutQuestionType(): void
    {
        $this->genericAnswerWriter->shouldReceive('write')
            ->with($this->qaContext, $this->qaElementValue, null)
            ->once();

        $this->baseAnswerSaver->save($this->qaContext, $this->postData);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSaveWithQuestionType')]
    public function testSaveWithQuestionType(mixed $questionType): void
    {
        $this->genericAnswerWriter->shouldReceive('write')
            ->with($this->qaContext, $this->qaElementValue, $questionType)
            ->once();

        $this->baseAnswerSaver->save($this->qaContext, $this->postData, $questionType);
    }

    public static function dpSaveWithQuestionType(): \Iterator
    {
        yield [Question::QUESTION_TYPE_STRING];
        yield [Question::QUESTION_TYPE_INTEGER];
        yield [Question::QUESTION_TYPE_BOOLEAN];
    }
}
