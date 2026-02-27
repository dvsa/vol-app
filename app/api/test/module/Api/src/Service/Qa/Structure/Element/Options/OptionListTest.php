<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Options;

use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\Option;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Options\OptionList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * OptionListTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class OptionListTest extends MockeryTestCase
{
    public const ITEM_1_VALUE = '1';
    public const ITEM_1_LABEL = 'Food';
    public const ITEM_1_HINT = 'Hint for the Food item';

    public const ITEM_2_VALUE = '3';
    public const ITEM_2_LABEL = 'Metals';
    public const ITEM_2_HINT = 'Hint for the Metals item';

    private $option1;

    private $option2;

    private $optionFactory;

    private $optionList;

    public function setUp(): void
    {
        $this->option1 = m::mock(Option::class);

        $this->option2 = m::mock(Option::class);

        $this->optionFactory = m::mock(OptionFactory::class);
        $this->optionFactory->shouldReceive('create')
            ->with(self::ITEM_1_VALUE, self::ITEM_1_LABEL, self::ITEM_1_HINT)
            ->once()
            ->andReturn($this->option1);
        $this->optionFactory->shouldReceive('create')
            ->with(self::ITEM_2_VALUE, self::ITEM_2_LABEL, self::ITEM_2_HINT)
            ->once()
            ->andReturn($this->option2);

        $this->optionList = new OptionList($this->optionFactory);
        $this->optionList->add(self::ITEM_1_VALUE, self::ITEM_1_LABEL, self::ITEM_1_HINT);
        $this->optionList->add(self::ITEM_2_VALUE, self::ITEM_2_LABEL, self::ITEM_2_HINT);
    }

    public function testGetRepresentation(): void
    {
        $option1Representation = [
            'value' => self::ITEM_1_VALUE,
            'label' => self::ITEM_1_LABEL,
            'hint' => self::ITEM_1_HINT,
        ];

        $option2Representation = [
            'value' => self::ITEM_2_VALUE,
            'label' => self::ITEM_2_LABEL,
            'hint' => self::ITEM_2_HINT,
        ];

        $this->option1->shouldReceive('getRepresentation')
            ->andReturn($option1Representation);

        $this->option2->shouldReceive('getRepresentation')
            ->andReturn($option2Representation);

        $expectedRepresentation = [
            $option1Representation,
            $option2Representation,
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $this->optionList->getRepresentation()
        );
    }

    public function testGetOptions(): void
    {
        $options = $this->optionList->getOptions();

        $this->assertCount(2, $options);
        $this->assertArrayHasKey(0, $options);
        $this->assertSame($this->option1, $options[0]);
        $this->assertArrayHasKey(1, $options);
        $this->assertSame($this->option2, $options[1]);
    }
}
