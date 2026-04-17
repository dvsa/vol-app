<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Options;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionListFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionListGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\SourceInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * OptionListGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class OptionListGeneratorTest extends MockeryTestCase
{
    private $directSource;

    private $optionListFactory;

    private $optionFactory;

    private $optionListGenerator;

    public function setUp(): void
    {
        $this->directSource = m::mock(SourceInterface::class);

        $this->optionListFactory = m::mock(OptionListFactory::class);

        $this->optionFactory = m::mock(OptionFactory::class);

        $this->optionListGenerator = new OptionListGenerator($this->optionListFactory, $this->optionFactory);

        $this->optionListGenerator->registerSource(
            'refData',
            m::mock(SourceInterface::class)
        );
        $this->optionListGenerator->registerSource(
            'direct',
            $this->directSource
        );
        $this->optionListGenerator->registerSource(
            'other',
            m::mock(SourceInterface::class)
        );
    }

    public function testGenerate(): void
    {
        $sourceName = 'direct';
        $sourceOptions = [
            'option1' => 'value1',
            'option2' => 'value2'
        ];

        $data = [
            'name' => $sourceName,
            'options' => $sourceOptions
        ];

        $optionList = m::mock(OptionList::class);

        $this->optionListFactory->shouldReceive('create')
            ->with($this->optionFactory)
            ->once()
            ->andReturn($optionList);

        $this->directSource->shouldReceive('populateOptionList')
            ->with($optionList, $sourceOptions)
            ->once();

        $this->assertEquals(
            $optionList,
            $this->optionListGenerator->generate($data)
        );
    }

    public function testExceptionOnUnknownSourceName(): void
    {
        $data = [
            'name' => 'test',
            'options' => [
                'key1' => 'value1',
                'key2' => 'value2'
            ]
        ];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No source found for name test');

        $this->optionListGenerator->generate($data);
    }
}
