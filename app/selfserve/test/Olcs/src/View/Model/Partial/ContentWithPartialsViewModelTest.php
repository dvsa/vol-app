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
    const AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED = 'Expected at least one partial to be provided';
    const THE_PARTIALS_VARIABLE = 'partials';
    const A_PARTIAL = 'A PARTIAL';
    const A_SECOND_PARTIAL = 'A SECOND PARTIAL';
    const THE_CONTENT_VARIABLE = 'content';
    const AN_EMPTY_CONTENT = '';
    const THE_CONTENT_WITH_PARTIALS_TEMPLATE = 'partials/content-with-partials';
    const OVERWRITE_VARIABLES = true;
    const AN_INVALID_PARTIALS_VALUE = 'AN_INVALID_PARTIALS_VALUE';

    /**
     * @var ContentWithPartialsViewModel
     */
    protected $sut;

    /**
     * @test
     */
    public function __construct_ThrowsException_IfNoPartialsAreProvided()
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->setUpSut([]);
    }

    /**
     * @test
     */
    public function __construct_ThrowsException_IfEmptyPartialsAreProvided()
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => []]);
    }

    /**
     * @test
     */
    public function __construct_SetsEmptyContent_IfNoneProvided()
    {
        // Execute
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Assert
        $this->assertEquals(static::AN_EMPTY_CONTENT, $this->sut->getVariable(static::THE_CONTENT_VARIABLE));
    }

    /**
     * @test
     */
    public function __construct_SetsTemplate()
    {
        // Execute
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Assert
        $this->assertEquals(static::THE_CONTENT_WITH_PARTIALS_TEMPLATE, $this->sut->getTemplate());
    }

    /**
     * @test
     */
    public function setVariable_IsCallable()
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Assert
        $this->assertIsCallable([$this->sut, 'setVariable']);
    }

    /**
     * @test
     * @depends setVariable_IsCallable
     */
    public function setVariable_SetsPartials()
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Execute
        $this->sut->setVariable(static::THE_PARTIALS_VARIABLE, [static::A_SECOND_PARTIAL]);

        // Assert
        $this->assertEquals([static::A_SECOND_PARTIAL], $this->sut->getVariable(static::THE_PARTIALS_VARIABLE));
    }

    /**
     * @test
     * @depends setVariable_IsCallable
     */
    public function setVariable_ThrowsException_WhenProvidingPartials_ThatAreEmpty()
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariable(static::THE_PARTIALS_VARIABLE, []);
    }

    /**
     * @test
     * @depends setVariable_IsCallable
     */
    public function setVariable_ThrowsException_WhenProvidingPartials_ThatAreNotAnArray()
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariable(static::THE_PARTIALS_VARIABLE, static::AN_INVALID_PARTIALS_VALUE);
    }

    /**
     * @test
     */
    public function setVariables_IsCallable()
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Assert
        $this->assertIsCallable([$this->sut, 'setVariables']);
    }

    /**
     * @test
     * @depends setVariables_IsCallable
     */
    public function setVariables_SetsPartials()
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Execute
        $this->sut->setVariableS([static::THE_PARTIALS_VARIABLE => [static::A_SECOND_PARTIAL]]);

        // Assert
        $this->assertEquals([static::A_SECOND_PARTIAL], $this->sut->getVariable(static::THE_PARTIALS_VARIABLE));
    }

    /**
     * @test
     * @depends setVariables_IsCallable
     */
    public function setVariables_ThrowsException_WhenAddingPartialsThatAreEmpty()
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariables([static::THE_PARTIALS_VARIABLE => []]);
    }

    /**
     * @test
     * @depends setVariables_IsCallable
     */
    public function setVariables_ThrowsExceptionWhenOverwriting_WithoutPartials()
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariables([], static::OVERWRITE_VARIABLES);
    }

    /**
     * @test
     * @depends setVariables_IsCallable
     */
    public function setVariables_ThrowsExceptionWhenOverwriting_WithInvalidPartials()
    {
        // Setup
        $this->setUpSut([static::THE_PARTIALS_VARIABLE => [static::A_PARTIAL]]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::AN_EXCEPTION_MESSAGE_WHEN_NO_PARTIALS_ARE_PROVIDED);

        // Execute
        $this->sut->setVariables([static::THE_PARTIALS_VARIABLE => static::AN_INVALID_PARTIALS_VALUE], static::OVERWRITE_VARIABLES);
    }

    /**
     * @test
     * @depends setVariables_IsCallable
     */
    public function setVariables_ThrowsExceptionWhenOverwriting_WithEmptyPartials()
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
     * @param mixed ...$args
     * @return ContentWithPartialsViewModel
     */
    protected function setUpSut(...$args): ContentWithPartialsViewModel
    {
        return $this->sut = new ContentWithPartialsViewModel(...$args);
    }
}
