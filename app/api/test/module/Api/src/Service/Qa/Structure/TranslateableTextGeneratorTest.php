<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableText;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextParameter;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextParameterGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TranslateableTextGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TranslateableTextGeneratorTest extends MockeryTestCase
{
    private $optionsKey;

    private $translateableText;

    private $translateableTextFactory;

    private $translateableTextParameterGenerator;

    private $translateableTextGenerator;

    public function setUp(): void
    {
        $this->optionsKey = 'optionsKey';

        $this->translateableText = m::mock(TranslateableText::class);

        $this->translateableTextFactory = m::mock(TranslateableTextFactory::class);
        $this->translateableTextFactory->shouldReceive('create')
            ->with($this->optionsKey)
            ->andReturn($this->translateableText);

        $this->translateableTextParameterGenerator = m::mock(TranslateableTextParameterGenerator::class);

        $this->translateableTextGenerator = new TranslateableTextGenerator(
            $this->translateableTextFactory,
            $this->translateableTextParameterGenerator
        );
    }

    public function testGenerateWithNoParameters(): void
    {
        $options = [
            'key' => $this->optionsKey
        ];

        $this->assertSame(
            $this->translateableText,
            $this->translateableTextGenerator->generate($options)
        );
    }

    public function testGenerateWithParameters(): void
    {
        $translateableTextParameter1 = m::mock(TranslateableTextParameter::class);

        $parameter1 = [
            'value' => 'parameter1Value',
            'formatter' => 'parameter1Formatter'
        ];

        $translateableTextParameter2 = m::mock(TranslateableTextParameter::class);

        $parameter2 = [
            'value' => 'parameter2Value',
            'formatter' => 'parameter2Formatter'
        ];

        $this->translateableTextParameterGenerator->shouldReceive('generate')
            ->with($parameter1)
            ->once()
            ->andReturn($translateableTextParameter1);

        $this->translateableTextParameterGenerator->shouldReceive('generate')
            ->with($parameter2)
            ->once()
            ->andReturn($translateableTextParameter2);

        $options = [
            'key' => $this->optionsKey,
            'parameters' => [
                $parameter1,
                $parameter2
            ]
        ];

        $this->translateableText->shouldReceive('addParameter')
            ->with($translateableTextParameter1)
            ->once()
            ->ordered();
        $this->translateableText->shouldReceive('addParameter')
            ->with($translateableTextParameter2)
            ->once()
            ->ordered();

        $this->assertSame(
            $this->translateableText,
            $this->translateableTextGenerator->generate($options)
        );
    }
}
