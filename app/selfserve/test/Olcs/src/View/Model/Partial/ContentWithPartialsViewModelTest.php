<?php

declare(strict_types=1);

namespace OlcsTest\View\Model\Element;

use Common\Test\MockeryTestCase;
use Olcs\View\Model\Partial\ContentWithPartialsViewModel;
use InvalidArgumentException;

/**
 * @see ContentWithPartialsViewModel
 */
class ContentWithPartialsViewModelTest extends MockeryTestCase
{
    public const AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED = 'Expected at least one partial to be provided';
    public const THE_PARTIALS_VARIABLE = 'partials';
    public const A_PARTIAL = 'A PARTIAL';
    public const A_SECOND_PARTIAL = 'A SECOND PARTIAL';
    public const THE_CONTENT_VARIABLE = 'content';
    public const AN_EMPTY_CONTENT = '';
    public const THE_CONTENT_WITH_PARTIALS_TEMPLATE = 'partials/content-with-partials';
    public const OVERWRITE_VARIABLES = true;
    public const AN_INVALID_PARTIALS_VALUE = 'AN_INVALID_PARTIALS_VALUE';

    /**
     * @var ContentWithPartialsViewModel
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructThrowsExceptionIfNoPartialsAreProvided(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->setUpSut([]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructThrowsExceptionIfEmptyPartialsAreProvided(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => []]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructSetsEmptyContentIfNoneProvided(): void
    {
        // Execute
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Assert
        $this->assertEquals(static::AN_EMPTY_CONTENT, $this->sut->getVariable(static::THE_CONTENT_VARIABLE));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructSetsTemplate(): void
    {
        // Execute
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Assert
        $this->assertEquals(static::THE_CONTENT_WITH_PARTIALS_TEMPLATE, $this->sut->getTemplate());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariableIsCallable(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Assert
        $this->assertIsCallable($this->sut->setVariable(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariableIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariableSetsPartials(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Execute
        $this->sut->setVariable(static::THE_PARTIALS_VARIABLE, [static::A_SECOND_PARTIAL]);

        // Assert
        $this->assertEquals([static::A_SECOND_PARTIAL], $this->sut->getVariable(static::THE_PARTIALS_VARIABLE));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariableIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariableThrowsExceptionWhenProvidingPartialsThatAreEmpty(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariable(static::THE_PARTIALS_VARIABLE, []);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariableIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariableThrowsExceptionWhenProvidingPartialsThatAreNotAnArray(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariable(static::THE_PARTIALS_VARIABLE, static::AN_INVALID_PARTIALS_VALUE);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesIsCallable(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Assert
        $this->assertIsCallable($this->sut->setVariables(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesSetsPartials(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Execute
        $this->sut->setVariableS([static::THE_PARTIALS_VARIABLE => [static::A_SECOND_PARTIAL]]);

        // Assert
        $this->assertEquals([static::A_SECOND_PARTIAL], $this->sut->getVariable(static::THE_PARTIALS_VARIABLE));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesThrowsExceptionWhenAddingPartialsThatAreEmpty(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariables([static::THE_PARTIALS_VARIABLE => []]);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesThrowsExceptionWhenOverwritingWithoutPartials(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariables([], static::OVERWRITE_VARIABLES);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesThrowsExceptionWhenOverwritingWithInvalidPartials(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariables([static::THE_PARTIALS_VARIABLE => static::AN_INVALID_PARTIALS_VALUE], static::OVERWRITE_VARIABLES);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesThrowsExceptionWhenOverwritingWithEmptyPartials(): void
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariables([static::THE_PARTIALS_VARIABLE => []], static::OVERWRITE_VARIABLES);
    }

    /**
     * @return ContentWithPartialsViewModel
     */
    protected function setUpSut(mixed ...$args): ContentWithPartialsViewModel
    {
        return $this->sut = new ContentWithPartialsViewModel(...$args);
    }
}
