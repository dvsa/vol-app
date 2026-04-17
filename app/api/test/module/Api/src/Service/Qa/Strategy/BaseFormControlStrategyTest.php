<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Strategy;

use Dvsa\Olcs\Api\Service\Qa\AnswersSummary\AnswerSummaryProviderInterface;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\QaEntityInterface;
use Dvsa\Olcs\Api\Service\Qa\Strategy\BaseFormControlStrategy;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerClearerInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\AnswerSaverInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionText;
use Dvsa\Olcs\Api\Service\Qa\Structure\QuestionText\QuestionTextGeneratorInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * BaseFormControlStrategyTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class BaseFormControlStrategyTest extends MockeryTestCase
{
    private $frontendType;

    private $elementGenerator;

    private $answerSaver;

    private $answerClearer;

    private $questionTextGenerator;

    private $answerSummaryProvider;

    private $baseFormControlStrategy;

    public function setUp(): void
    {
        $this->frontendType = 'checkbox';

        $this->elementGenerator = m::mock(ElementGeneratorInterface::class);

        $this->answerSaver = m::mock(AnswerSaverInterface::class);

        $this->answerClearer = m::mock(AnswerClearerInterface::class);

        $this->questionTextGenerator = m::mock(QuestionTextGeneratorInterface::class);

        $this->answerSummaryProvider = m::mock(AnswerSummaryProviderInterface::class);

        $this->baseFormControlStrategy = new BaseFormControlStrategy(
            $this->frontendType,
            $this->elementGenerator,
            $this->answerSaver,
            $this->answerClearer,
            $this->questionTextGenerator,
            $this->answerSummaryProvider
        );
    }

    public function testGetFrontendType(): void
    {
        $this->assertEquals(
            $this->frontendType,
            $this->baseFormControlStrategy->getFrontendType()
        );
    }

    public function testGetElement(): void
    {
        $qaEntity = m::mock(QaEntityInterface::class);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);

        $element = m::mock(ElementInterface::class);

        $this->elementGenerator->shouldReceive('supports')
            ->with($qaEntity)
            ->andReturn(true);
        $this->elementGenerator->shouldReceive('generate')
            ->with($elementGeneratorContext)
            ->andReturn($element);

        $this->assertSame(
            $element,
            $this->baseFormControlStrategy->getElement($elementGeneratorContext)
        );
    }

    public function testGetElementNotSupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Element generator does not support entity type');

        $qaEntity = m::mock(QaEntityInterface::class);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);

        $this->elementGenerator->shouldReceive('supports')
            ->with($qaEntity)
            ->andReturn(false);

        $this->baseFormControlStrategy->getElement($elementGeneratorContext);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSaveFormData')]
    public function testSaveFormData(mixed $inDestinationName, mixed $outDestinationName): void
    {
        $qaEntity = m::mock(QaEntityInterface::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);

        $postData = [
            'fields123' => [
                'cabotage' => '1'
            ]
        ];

        $this->answerSaver->shouldReceive('supports')
            ->with($qaEntity)
            ->andReturnTrue();
        $this->answerSaver->shouldReceive('save')
            ->with($qaContext, $postData)
            ->once()
            ->andReturn($inDestinationName);

        $this->assertEquals(
            $outDestinationName,
            $this->baseFormControlStrategy->saveFormData($qaContext, $postData)
        );
    }

    public static function dpSaveFormData(): array
    {
        return [
            [null, BaseFormControlStrategy::FRONTEND_DESTINATION_NEXT_STEP],
            ['DESTINATION_NAME', 'DESTINATION_NAME']
        ];
    }

    public function testSaveFormDataNotSupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Answer saver does not support entity type');

        $qaEntity = m::mock(QaEntityInterface::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);

        $postData = [
            'fields123' => [
                'cabotage' => '1'
            ]
        ];

        $this->answerSaver->shouldReceive('supports')
            ->with($qaEntity)
            ->andReturnFalse();

        $this->baseFormControlStrategy->saveFormData($qaContext, $postData);
    }

    public function testClearAnswer(): void
    {
        $qaEntity = m::mock(QaEntityInterface::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);

        $this->answerClearer->shouldReceive('supports')
            ->with($qaEntity)
            ->andReturnTrue();
        $this->answerClearer->shouldReceive('clear')
            ->with($qaContext)
            ->once();

        $this->baseFormControlStrategy->clearAnswer($qaContext);
    }

    public function testClearAnswerNotSupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Answer clearer does not support entity type');

        $qaEntity = m::mock(QaEntityInterface::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);

        $this->answerClearer->shouldReceive('supports')
            ->with($qaEntity)
            ->andReturnFalse();

        $this->baseFormControlStrategy->clearAnswer($qaContext);
    }

    public function testGetQuestionText(): void
    {
        $qaEntity = m::mock(QaEntityInterface::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);

        $questionText = m::mock(QuestionText::class);

        $this->questionTextGenerator->shouldReceive('supports')
            ->with($qaEntity)
            ->andReturnTrue();
        $this->questionTextGenerator->shouldReceive('generate')
            ->with($qaContext)
            ->andReturn($questionText);

        $this->assertSame(
            $questionText,
            $this->baseFormControlStrategy->getQuestionText($qaContext)
        );
    }

    public function testGetQuestionTextNotSupported(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Question text generator does not support entity type');

        $qaEntity = m::mock(QaEntityInterface::class);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($qaEntity);

        $this->questionTextGenerator->shouldReceive('supports')
            ->with($qaEntity)
            ->andReturnFalse();

        $this->baseFormControlStrategy->getQuestionText($qaContext);
    }

    public function getAnswerSummaryProvider(): array
    {
        $this->assertSame(
            $this->answerSummaryProvider,
            $this->baseFormControlStrategy->getAnswerSummaryProvider()
        );
    }
}
