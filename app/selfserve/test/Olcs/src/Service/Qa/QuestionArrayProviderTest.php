<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Common\Service\Qa\FormattedTranslateableTextParametersGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\QuestionArrayProvider;
use RuntimeException;

class QuestionArrayProviderTest extends MockeryTestCase
{
    private $formattedTranslateableTextParametersGenerator;

    private $sut;

    public function setUp(): void
    {
        $this->formattedTranslateableTextParametersGenerator = m::mock(
            FormattedTranslateableTextParametersGenerator::class
        );

        $this->sut = new QuestionArrayProvider($this->formattedTranslateableTextParametersGenerator);
    }

    public function testGetWithHtmlEscapeFilter(): void
    {
        $questionKey = 'questionKey';

        $questionParameters = [
            [
                [
                    'value' => '38.00',
                    'formatter' => 'currency'
                ],
                [
                    'value' => 'questionParameter2'
                ]
            ]
        ];

        $formattedQuestionParameters = ['38', 'questionParameter2'];

        $question = [
            'filter' => 'htmlEscape',
            'translateableText' => [
                'key' => $questionKey,
                'parameters' => $questionParameters
            ]
        ];

        $this->formattedTranslateableTextParametersGenerator->shouldReceive('generate')
            ->with($questionParameters)
            ->andReturn($formattedQuestionParameters);

        $expectedArray = [
            'question' => $questionKey,
            'questionArgs' => $formattedQuestionParameters
        ];

        $this->assertEquals(
            $expectedArray,
            $this->sut->get($question)
        );
    }

    public function testExceptionOnRawFilter(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(QuestionArrayProvider::ONLY_HTML_ESCAPE_SUPPORTED);

        $question = [
            'filter' => 'raw',
            'translateableText' => [
                'key' => 'questionKey',
                'parameters' => [
                    [
                        'value' => '38.00',
                        'formatter' => 'currency'
                    ],
                    [
                        'value' => 'questionParameter2'
                    ]
                ]
            ]
        ];

        $this->sut->get($question);
    }
}
