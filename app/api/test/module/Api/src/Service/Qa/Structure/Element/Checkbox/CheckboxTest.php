<?php

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Checkbox;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox\Checkbox;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CheckboxTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CheckboxTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestGetRepresentation
     */
    public function testGetRepresentation($checked)
    {
        $labelTranslateableTextRepresentation = ['labelTranslateableTextRepresentation'];

        $labelTranslateableText = m::mock(TranslateableText::class);
        $labelTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($labelTranslateableTextRepresentation);

        $notCheckedMessageTranslateableTextRepresentation = ['notCheckedMessageTranslateableTextRepresentation'];

        $notCheckedMessageTranslateableText = m::mock(TranslateableText::class);
        $notCheckedMessageTranslateableText->shouldReceive('getRepresentation')
            ->andReturn($notCheckedMessageTranslateableTextRepresentation);

        $checkbox = new Checkbox(
            $labelTranslateableText,
            $notCheckedMessageTranslateableText,
            $checked
        );

        $expectedRepresentation = [
            'label' => $labelTranslateableTextRepresentation,
            'notCheckedMessage' => $notCheckedMessageTranslateableTextRepresentation,
            'checked' => $checked
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $checkbox->getRepresentation()
        );
    }

    public function dpTestGetRepresentation()
    {
        return [
            [true],
            [false]
        ];
    }
}
