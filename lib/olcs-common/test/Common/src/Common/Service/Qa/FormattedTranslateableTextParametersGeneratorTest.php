<?php

namespace CommonTest\Service\Qa;

use Common\Service\Qa\FormattedTranslateableTextParametersGenerator;
use Common\Service\Qa\TranslateableTextParameterHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * FormattedTranslateableTextParametersGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FormattedTranslateableTextParametersGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $parameter1 = [
            'value' => '12',
            'formatter' => 'currency'
        ];

        $parameter2 = [
            'value' => '38'
        ];

        $parameters = [
            $parameter1,
            $parameter2
        ];

        $formattedParameter1 = '12';
        $formattedParameter2 = '38.00';

        $translateableTextParameterHandler = m::mock(TranslateableTextParameterHandler::class);
        $translateableTextParameterHandler->shouldReceive('handle')
            ->with($parameter1)
            ->once()
            ->andReturn($formattedParameter1);
        $translateableTextParameterHandler->shouldReceive('handle')
            ->with($parameter2)
            ->once()
            ->andReturn($formattedParameter2);

        $sut = new FormattedTranslateableTextParametersGenerator($translateableTextParameterHandler);

        $this->assertEquals(
            [$formattedParameter1, $formattedParameter2],
            $sut->generate($parameters)
        );
    }
}
