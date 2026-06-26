<?php

namespace CommonTest\Service\Qa;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\FormattedTranslateableTextParametersGenerator;
use Common\Service\Qa\TranslateableTextHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TranslateableTextHandlerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TranslateableTextHandlerTest extends MockeryTestCase
{
    public function testTranslate(): void
    {
        $translateableTextKey = 'textKey';

        $translateableTextParameters = [
            [
                'value' => '38.00',
                'formatter' => 'currency'
            ],
            [
                'textParam2'
            ]
        ];

        $formattedTranslateableTextParameters = ['38', 'textParam2'];

        $translateableText = [
            'key' => $translateableTextKey,
            'parameters' => $translateableTextParameters
        ];

        $translated = 'testTranslateableText';

        $translationHelper = m::mock(TranslationHelperService::class);
        $translationHelper->shouldReceive('translateReplace')
            ->with($translateableTextKey, $formattedTranslateableTextParameters)
            ->andReturn($translated);

        $formattedTranslateableTextParametersGenerator = m::mock(FormattedTranslateableTextParametersGenerator::class);
        $formattedTranslateableTextParametersGenerator->shouldReceive('generate')
            ->with($translateableTextParameters)
            ->andReturn($formattedTranslateableTextParameters);

        $sut = new TranslateableTextHandler(
            $formattedTranslateableTextParametersGenerator,
            $translationHelper
        );

        $this->assertEquals(
            $translated,
            $sut->translate($translateableText)
        );
    }
}
