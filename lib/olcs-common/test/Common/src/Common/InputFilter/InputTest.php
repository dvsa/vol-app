<?php

declare(strict_types=1);

namespace CommonTest\InputFilter;

use Common\InputFilter\Input;
use Laminas\Filter\FilterChain;
use Common\Test\MockeryTestCase;

/**
 * @see Input
 */
final class InputTest extends MockeryTestCase
{
    protected const string AN_INPUT_NAME = 'AN INPUT NAME';

    protected const string A_RAW_INPUT_VALUE = 'A RAW INPUT VALUE';

    protected const string A_SECOND_RAW_INPUT_VALUE = 'A SECOND RAW INPUT VALUE';

    protected const string A_FILTERED_INPUT_VALUE = 'A FILTERED INPUT VALUE';

    protected const string A_SECOND_FILTERED_INPUT_VALUE = 'A SECOND FILTERED INPUT VALUE';

    /**
     * @var Input
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function setValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(function ($value): void {
            $this->sut->setValue($value);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn() => $this->sut->getValue());
    }

    #[\PHPUnit\Framework\Attributes\Depends('getValueIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Depends('getValueFiltersValue')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Depends('getValueIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setValueIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
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
