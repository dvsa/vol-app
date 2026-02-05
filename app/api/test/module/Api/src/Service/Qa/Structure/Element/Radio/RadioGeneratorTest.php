<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Radio;

use Dvsa\Olcs\Api\Entity\Generic\ApplicationStep as ApplicationStepEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionListGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\Radio;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\RadioFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\RadioGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RadioGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RadioGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $answerValue = 'permit_app_uc';

        $notSelectedMessageOptions = [
            'key' => 'notSelectedMessageKey',
            'parameters' => [
                'notSelectedMessageParameter1',
                'notSelectedMessageParameter2'
            ]
        ];

        $sourceOptions = [
            'name' => 'direct',
            'options' => [
                'key1' => 'value1',
                'key2' => 'value2'
            ]
        ];

        $optionList = m::mock(OptionList::class);

        $options = [
            'notSelectedMessage' => $notSelectedMessageOptions,
            'source' => $sourceOptions
        ];

        $notSelectedMessageTranslateableText = m::mock(TranslateableText::class);

        $radio = m::mock(Radio::class);

        $radioFactory = m::mock(RadioFactory::class);
        $radioFactory->shouldReceive('create')
            ->with($optionList, $notSelectedMessageTranslateableText, $answerValue)
            ->andReturn($radio);

        $applicationStepEntity = m::mock(ApplicationStepEntity::class);
        $applicationStepEntity->shouldReceive('getDecodedOptionSource')
            ->andReturn($options);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getApplicationStepEntity')
            ->andReturn($applicationStepEntity);
        $elementGeneratorContext->shouldReceive('getAnswerValue')
            ->withNoArgs()
            ->andReturn($answerValue);

        $optionListGenerator = m::mock(OptionListGenerator::class);
        $optionListGenerator->shouldReceive('generate')
            ->with($sourceOptions)
            ->andReturn($optionList);

        $translateableTextGenerator = m::mock(TranslateableTextGenerator::class);
        $translateableTextGenerator->shouldReceive('generate')
            ->with($notSelectedMessageOptions)
            ->andReturn($notSelectedMessageTranslateableText);

        $radioGenerator = new RadioGenerator($radioFactory, $optionListGenerator, $translateableTextGenerator);

        $this->assertSame(
            $radio,
            $radioGenerator->generate($elementGeneratorContext)
        );
    }
}
