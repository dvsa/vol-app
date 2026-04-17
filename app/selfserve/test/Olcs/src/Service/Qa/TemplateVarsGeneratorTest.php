<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\GuidanceTemplateVarsAdder;
use Olcs\Service\Qa\QuestionArrayProvider;
use Olcs\Service\Qa\TemplateVarsGenerator;

class TemplateVarsGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $untranslatedQuestionData = [
            'filter' => 'htmlEscape',
            'translateableText' => [
                'key' => 'questionKey',
                'parameters' => []
            ]
        ];

        $questionText = [
            'question' => $untranslatedQuestionData
        ];

        $baseTemplateVars = [
            'question' => 'transformedQuestionData'
        ];

        $baseTemplateVarsWithGuidance = [
            'question' => 'transformedQuestionData',
            'guidance' => 'transformedGuidanceData',
        ];

        $baseTemplateVarsWithAdditionalGuidance = [
            'question' => 'transformedQuestionData',
            'guidance' => 'transformedGuidanceData',
            'additionalGuidance' => 'transformedAdditionalGuidanceData',
        ];

        $questionArrayProvider = m::mock(QuestionArrayProvider::class);
        $questionArrayProvider->shouldReceive('get')
            ->with($untranslatedQuestionData)
            ->once()
            ->andReturn($baseTemplateVars);

        $guidanceTemplateVarsAdder = m::mock(GuidanceTemplateVarsAdder::class);
        $guidanceTemplateVarsAdder->shouldReceive('add')
            ->with($baseTemplateVars, $questionText, 'guidance')
            ->once()
            ->andReturn($baseTemplateVarsWithGuidance);
        $guidanceTemplateVarsAdder->shouldReceive('add')
            ->with($baseTemplateVarsWithGuidance, $questionText, 'additionalGuidance')
            ->once()
            ->andReturn($baseTemplateVarsWithAdditionalGuidance);

        $sut = new TemplateVarsGenerator($questionArrayProvider, $guidanceTemplateVarsAdder);

        $this->assertEquals(
            $baseTemplateVarsWithAdditionalGuidance,
            $sut->generate($questionText)
        );
    }
}
