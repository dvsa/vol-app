<?php

declare(strict_types=1);

namespace OlcsTest\View\Model\Element;

use Common\Test\MockeryTestCase;
use Olcs\View\Model\Element\AnchorViewModel;
use InvalidArgumentException;

/**
 * @see AnchorViewModel
 */
class AnchorViewModelTest extends MockeryTestCase
{
    protected const A_URL = 'A URL';
    protected const A_ROUTE = 'A ROUTE';
    protected const A_CUSTOM_CLASS = 'A CLASS';
    protected const THE_CLASS_VARIABLE_KEY = 'class';
    protected const THE_ROUTE_VARIABLE_KEY = 'route';
    protected const THE_URL_VARIABLE_KEY = 'url';
    protected const GOVUK_LINK_CLASS = 'govuk-link';
    protected const EXCEPTION_MESSAGE_WHEN_BOTH_URL_AND_ROUTE_VARIABLES_ARE_PROVIDED = 'Expected "url" variable or "route" variable but received both';
    protected const EXCEPTION_MESSAGE_WHEN_SETTING_URL_WHILE_ROUTE_IS_ALREADY_SET = 'Unable to set "url" while "route" is set';
    protected const EXCEPTION_MESSAGE_WHEN_SETTING_ROUTE_WHILE_URL_IS_ALREADY_SET = 'Unable to set "route" while "url" is set';
    protected const OVERWRITE_EXISTING_VARIABLES = true;
    protected const ANCHOR_TEMPLATE = 'element/anchor';

    /**
     * @var AnchorViewModel
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructThrowsExceptionIfRouteAndUrlAreProvided(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_BOTH_URL_AND_ROUTE_VARIABLES_ARE_PROVIDED);

        // Execute
        $this->setUpSut([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE, static::THE_URL_VARIABLE_KEY => static::A_URL]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructSetsTemplate(): void
    {
        // Execute
        $this->setUpSut();

        // Assert
        $this->assertEquals(static::ANCHOR_TEMPLATE, $this->sut->getTemplate());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructAppliesADefaultClass(): void
    {
        // Execute
        $this->setUpSut();

        // Assert
        $this->assertEquals(static::GOVUK_LINK_CLASS, $this->sut->getVariable(static::THE_CLASS_VARIABLE_KEY));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function constructOverridesDefaultClassWhenCustomClassIsProvided(): void
    {
        // Execute
        $this->setUpSut([static::THE_CLASS_VARIABLE_KEY => static::A_CUSTOM_CLASS]);

        // Assert
        $this->assertEquals(static::A_CUSTOM_CLASS, $this->sut->getVariable(static::THE_CLASS_VARIABLE_KEY));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariableisCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->setVariable(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariableisCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariableThrowsExceptionIfRouteIsProvidedWhileUrlIsAlreadySet(): void
    {
        // Setup
        $this->setUpSut([static::THE_URL_VARIABLE_KEY => static::A_URL]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_SETTING_ROUTE_WHILE_URL_IS_ALREADY_SET);

        // Execute
        $this->sut->setVariable(static::THE_ROUTE_VARIABLE_KEY, static::A_ROUTE);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariableisCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariableThrowsExceptionIfUrlIsProvidedWhileRouteIsAlreadySet(): void
    {
        // Setup
        $this->setUpSut([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_SETTING_URL_WHILE_ROUTE_IS_ALREADY_SET);

        // Execute
        $this->sut->setVariable(static::THE_URL_VARIABLE_KEY, static::A_URL);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesisCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable($this->sut->setVariables(...));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesisCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesThrowsExceptionIfRouteAndUrlAreProvided(): void
    {
        // Setup
        $this->setUpSut();

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_BOTH_URL_AND_ROUTE_VARIABLES_ARE_PROVIDED);

        // Execute
        $this->sut->setVariables([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE, static::THE_URL_VARIABLE_KEY => static::A_URL]);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesisCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesOverwritesRouteWhenUrlIsAlreadySet(): void
    {
        // Setup
        $this->setUpSut([static::THE_URL_VARIABLE_KEY => static::A_URL]);

        // Execute
        $this->sut->setVariables([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE], static::OVERWRITE_EXISTING_VARIABLES);

        // Assert
        $this->assertEquals(static::A_ROUTE, $this->sut->getVariable(static::THE_ROUTE_VARIABLE_KEY));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesisCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesThrowsExceptionIfNotOverwritingAndVariablesContainsRouteWhileUrlIsAlreadySet(): void
    {
        // Setup
        $this->setUpSut([static::THE_URL_VARIABLE_KEY => static::A_URL]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_SETTING_ROUTE_WHILE_URL_IS_ALREADY_SET);

        // Execute
        $this->sut->setVariables([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE]);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesisCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesOverwritesUrlWhenRouteIsAlreadySet(): void
    {
        // Setup
        $this->setUpSut([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE]);

        // Execute
        $this->sut->setVariables([static::THE_URL_VARIABLE_KEY => static::A_URL], static::OVERWRITE_EXISTING_VARIABLES);

        // Assert
        $this->assertEquals(static::A_URL, $this->sut->getVariable(static::THE_URL_VARIABLE_KEY));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setVariablesisCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setVariablesThrowsExceptionIfNotOverwritingAndVariablesContainsUrlWhileRouteIsAlreadySet(): void
    {
        // Setup
        $this->setUpSut([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_SETTING_URL_WHILE_ROUTE_IS_ALREADY_SET);

        // Execute
        $this->sut->setVariables([static::THE_URL_VARIABLE_KEY => static::A_URL]);
    }

    /**
     * @return AnchorViewModel
     */
    protected function setUpSut(mixed ...$args): AnchorViewModel
    {
        return $this->sut = new AnchorViewModel(...$args);
    }
}
