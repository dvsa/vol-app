<?php

namespace CommonTest\InputFilter;

use Common\InputFilter\Input;
use Laminas\Filter\FilterChain;
use Common\Test\MockeryTestCase;

/**
 * @see Input
 */
class InputTest extends MockeryTestCase
{
    protected const AN_INPUT_NAME = 'AN INPUT NAME';

    protected const A_RAW_INPUT_VALUE = 'A RAW INPUT VALUE';

    protected const A_SECOND_RAW_INPUT_VALUE = 'A SECOND RAW INPUT VALUE';

    protected const A_FILTERED_INPUT_VALUE = 'A FILTERED INPUT VALUE';

    protected const A_SECOND_FILTERED_INPUT_VALUE = 'A SECOND FILTERED INPUT VALUE';

    /**
     * @var Input
     */
    protected $sut;

    /**
     * @test
     */
    public function setValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(function ($value): void {
            $this->sut->setValue($value);
        });
    }

    /**
     * @test
     */
    public function getValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn() => $this->sut->getValue());
    }

    /**
     * @test
     * @depends getValueIsCallable
     */
    public function getValueFiltersValue(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setFilterChain($this->aFilterChainThatReturns(static::A_FILTERED_INPUT_VALUE));
        $this->sut->setValue(static::A_RAW_INPUT_VALUE);

        // Execute
        $result = $this->sut->getValue();

        // Assert
        $this->assertSame(self::A_FILTERED_INPUT_VALUE, $result);
    }

    /**
     * @test
     * @depends getValueFiltersValue
     */
    public function getValueFiltersValueOnceWhenTheValueHasNotBeenSetAgain(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValue(static::A_RAW_INPUT_VALUE);

        // Execute
        $this->sut->getValue();
        $this->sut->setFilterChain($this->aFilterChainThatReturns(static::A_FILTERED_INPUT_VALUE));

        $result = $this->sut->getValue();

        // Assert
        $this->assertSame(static::A_RAW_INPUT_VALUE, $result);
    }

    /**
     * @test
     * @depends getValueIsCallable
     * @depends setValueIsCallable
     */
    public function getValueFiltersValueTwiceWhenTheValueHasBeenSetSinceFirstBeingGotten(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setValue(static::A_RAW_INPUT_VALUE);
        $this->sut->getValue();
        $this->sut->setFilterChain($this->aFilterChainThatReturns(static::A_FILTERED_INPUT_VALUE));
        $this->sut->setValue(static::A_RAW_INPUT_VALUE);

        $result = $this->sut->getValue();

        // Assert
        $this->assertSame(static::A_FILTERED_INPUT_VALUE, $result);
    }

    protected function setUpSut(mixed $name = self::AN_INPUT_NAME): void
    {
        $this->sut = new Input($name);
    }

    /**
     * @param $value
     */
    protected function aFilterChainThatReturns($value): FilterChain
    {
        $chain = new FilterChain();
        $chain->attach(static fn() => $value);
        return $chain;
    }
}
