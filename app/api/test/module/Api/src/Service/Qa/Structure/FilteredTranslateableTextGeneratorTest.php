<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\FilteredTranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\FilteredTranslateableTextFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\FilteredTranslateableTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * FilteredTranslateableTextGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FilteredTranslateableTextGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $optionFilter = 'htmlEscape';

        $optionTranslateableText = [
            'key' => 'translateableTextKey',
            'parameters' => [
                'translateableTextParameter1',
                'translateableTextParameter2'
            ]
        ];

        $options = [
            'filter' => $optionFilter,
            'translateableText' => $optionTranslateableText
        ];

        $translateableText = m::mock(TranslateableText::class);

        $filteredTranslateableText = m::mock(FilteredTranslateableText::class);

        $filteredTranslateableTextFactory = m::mock(FilteredTranslateableTextFactory::class);
        $filteredTranslateableTextFactory->shouldReceive('create')
            ->with($optionFilter, $translateableText)
            ->andReturn($filteredTranslateableText);

        $translateableTextGenerator = m::mock(TranslateableTextGenerator::class);
        $translateableTextGenerator->shouldReceive('generate')
            ->with($optionTranslateableText)
            ->andReturn($translateableText);

        $filteredTranslateableTextGenerator = new FilteredTranslateableTextGenerator(
            $filteredTranslateableTextFactory,
            $translateableTextGenerator
        );

        $this->assertSame(
            $filteredTranslateableText,
            $filteredTranslateableTextGenerator->generate($options)
        );
    }
}
