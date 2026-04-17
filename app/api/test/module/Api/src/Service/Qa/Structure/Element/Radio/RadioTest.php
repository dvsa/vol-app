<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Radio;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Radio\Radio;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RadioTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RadioTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $value = 'permit_app_uc';

        $optionsRepresentation = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $optionList = m::mock(OptionList::class);
        $optionList->shouldReceive('getRepresentation')
            ->withNoArgs()
            ->andReturn($optionsRepresentation);

        $notSelectedMessageTranslateableTextRepresentation = ['notSelectedMessageTranslateableTextRepresentation'];

        $notSelectedMessageTranslateableText = m::mock(TranslateableText::class);
        $notSelectedMessageTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($notSelectedMessageTranslateableTextRepresentation);

        $radio = new Radio(
            $optionList,
            $notSelectedMessageTranslateableText,
            $value
        );

        $expectedRepresentation = [
            'options' => $optionsRepresentation,
            'notSelectedMessage' => $notSelectedMessageTranslateableTextRepresentation,
            'value' => $value
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $radio->getRepresentation()
        );
    }
}
