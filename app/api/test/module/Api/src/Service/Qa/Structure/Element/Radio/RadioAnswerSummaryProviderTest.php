<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Radio;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementInterface;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\Option;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionListGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\RadioAnswerSummaryProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * RadioAnswerSummaryProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RadioAnswerSummaryProviderTest extends MockeryTestCase
{
    private $radioOption2;

    private $applicationStepEntity;

    private $qaContext;

    private $element;

    private $optionListGenerator;

    private $radioAnswerSummaryProvider;

    public function setUp(): void
    {
        $decodedOptionSourceSource = [
            'name' => 'database',
            'tableName' => 'items'
        ];

        $decodedOptionSource = [
            'source' => $decodedOptionSourceSource
        ];

        $radioOption1 = m::mock(Option::class);
        $radioOption1->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn('item1Value');

        $this->radioOption2 = m::mock(Option::class);
        $this->radioOption2->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn('item2Value');

        $radioOption3 = m::mock(Option::class);
        $radioOption3->shouldReceive('getValue')
            ->withNoArgs()
            ->andReturn('item3Value');

        $radioOptions = [$radioOption1, $this->radioOption2, $radioOption3];

        $this->applicationStepEntity = m::mock(ApplicationStepEntity::class);
        $this->applicationStepEntity->shouldReceive('getDecodedOptionSource')
            ->withNoArgs()
            ->andReturn($decodedOptionSource);

        $this->qaContext = m::mock(QaContext::class);
        $this->qaContext->shouldReceive('getApplicationStepEntity')
            ->withNoArgs()
            ->andReturn($this->applicationStepEntity);

        $this->element = m::mock(ElementInterface::class);

        $optionList = m::mock(OptionList::class);
        $optionList->shouldReceive('getOptions')
            ->withNoArgs()
            ->andReturn($radioOptions);

        $this->optionListGenerator = m::mock(OptionListGenerator::class);
        $this->optionListGenerator->shouldReceive('generate')
            ->with($decodedOptionSourceSource)
            ->andReturn($optionList);

        $this->radioAnswerSummaryProvider = new RadioAnswerSummaryProvider($this->optionListGenerator);
    }

    public function testGetTemplateName(): void
    {
        $this->assertEquals(
            'generic',
            $this->radioAnswerSummaryProvider->getTemplateName()
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGetTemplateVariables(mixed $isSnapshot): void
    {
        $qaAnswer = 'item2Value';
        $item2Label = 'item2Label';

        $this->radioOption2->shouldReceive('getLabel')
            ->withNoArgs()
            ->andReturn($item2Label);

        $this->qaContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($qaAnswer);

        $templateVariables = $this->radioAnswerSummaryProvider->getTemplateVariables(
            $this->qaContext,
            $this->element,
            $isSnapshot
        );

        $this->assertEquals(
            ['answer' => $item2Label],
            $templateVariables
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSnapshot')]
    public function testGetTemplateVariablesException(mixed $isSnapshot): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Answer not found in list of options');

        $qaAnswer = 'item4Value';

        $this->qaContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($qaAnswer);

        $this->radioAnswerSummaryProvider->getTemplateVariables($this->qaContext, $this->element, $isSnapshot);
    }

    public static function dpSnapshot(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
