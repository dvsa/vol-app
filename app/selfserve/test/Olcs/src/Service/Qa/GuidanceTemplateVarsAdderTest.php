<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Common\Service\Qa\TranslateableTextHandler;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\GuidanceTemplateVarsAdder;
use RuntimeException;

class GuidanceTemplateVarsAdderTest extends MockeryTestCase
{
    private $translateableTextHandler;

    private $sut;

    private $templateVars;

    private $guidanceTranslateableText;

    private $additionalGuidanceTranslateableText;

    private $questionText;

    public function setUp(): void
    {
        $this->translateableTextHandler = m::mock(TranslateableTextHandler::class);

        $this->sut = new GuidanceTemplateVarsAdder($this->translateableTextHandler);

        $this->templateVars = [
            'variable1Key' => 'variable1Value',
            'variable2Key' => 'variable2Value'
        ];

        $this->guidanceTranslateableText = [
            'key' => 'guidanceKey',
            'parameters' => [
                'guidanceParameter1',
                'guidanceParameter2'
            ]
        ];

        $this->additionalGuidanceTranslateableText = [
            'key' => 'additionalGuidanceKey',
            'parameters' => [
                'additionalGuidanceParameter1',
                'additionalGuidanceParameter2'
            ]
        ];

        $this->questionText = [
            'guidance' => [
                'filter' => 'htmlEscape',
                'translateableText' => $this->guidanceTranslateableText
            ],
            'additionalGuidance' => [
                'filter' => 'raw',
                'translateableText' => $this->additionalGuidanceTranslateableText
            ],
            'unhandledFilterTest' => [
                'filter' => 'currency',
                'translateableText' => [
                    'key' => 'unhandledFilterTestKey',
                    'parameters' => []
                ]
            ]
        ];
    }

    public function testAddWithHtmlEscapeFilter(): void
    {
        $guidanceTranslatedText = 'Guidance translated text';

        $expectedTemplateVars = [
            'variable1Key' => 'variable1Value',
            'variable2Key' => 'variable2Value',
            'guidance' => $guidanceTranslatedText
        ];

        $this->translateableTextHandler->shouldReceive('translate')
            ->with($this->guidanceTranslateableText)
            ->andReturn($guidanceTranslatedText);

        $this->assertEquals(
            $expectedTemplateVars,
            $this->sut->add($this->templateVars, $this->questionText, 'guidance')
        );
    }

    public function testAddWithRawFilter(): void
    {
        $additionalGuidanceTranslatedText = 'Additional guidance translated text';

        $expectedTemplateVars = [
            'variable1Key' => 'variable1Value',
            'variable2Key' => 'variable2Value',
            'additionalGuidance' => [
                'disableHtmlEscape' => true,
                'value' => $additionalGuidanceTranslatedText
            ]
        ];

        $this->translateableTextHandler->shouldReceive('translate')
            ->with($this->additionalGuidanceTranslateableText)
            ->andReturn($additionalGuidanceTranslatedText);

        $this->assertEquals(
            $expectedTemplateVars,
            $this->sut->add($this->templateVars, $this->questionText, 'additionalGuidance')
        );
    }

    public function testAddWithSourceArrayElementNotPresent(): void
    {
        $this->assertEquals(
            $this->templateVars,
            $this->sut->add($this->templateVars, $this->questionText, 'unknownElement')
        );
    }

    public function testUnhandledFilterName(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unhandled filter name currency');

        $this->sut->add($this->templateVars, $this->questionText, 'unhandledFilterTest');
    }
}
