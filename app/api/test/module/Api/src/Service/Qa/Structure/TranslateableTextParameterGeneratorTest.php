<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextParameter;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextParameterFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\TranslateableTextParameterGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TranslateableTextParameterGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class TranslateableTextParameterGeneratorTest extends MockeryTestCase
{
    private $optionsValue;

    private $optionsFormatter;

    private $translateableTextParameter;

    private $translateableTextParameterFactory;

    private $translateableTextParameterGenerator;

    public function setUp(): void
    {
        $this->optionsValue = 'optionsValue';
        $this->optionsFormatter = 'optionsFormatter';

        $this->translateableTextParameter = m::mock(TranslateableTextParameter::class);

        $this->translateableTextParameterFactory = m::mock(TranslateableTextParameterFactory::class);

        $this->translateableTextParameterGenerator = new TranslateableTextParameterGenerator(
            $this->translateableTextParameterFactory
        );
    }

    public function testGenerateWithFormatter(): void
    {
        $options = [
            'value' => $this->optionsValue,
            'formatter' => $this->optionsFormatter
        ];

        $this->translateableTextParameterFactory->shouldReceive('create')
            ->with($this->optionsValue, $this->optionsFormatter)
            ->andReturn($this->translateableTextParameter);

        $this->assertSame(
            $this->translateableTextParameter,
            $this->translateableTextParameterGenerator->generate($options)
        );
    }

    public function testGenerateWithoutFormatter(): void
    {
        $options = [
            'value' => $this->optionsValue
        ];

        $this->translateableTextParameterFactory->shouldReceive('create')
            ->with($this->optionsValue, null)
            ->andReturn($this->translateableTextParameter);

        $this->assertSame(
            $this->translateableTextParameter,
            $this->translateableTextParameterGenerator->generate($options)
        );
    }
}
