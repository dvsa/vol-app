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

    /**
     * @test
     */
    public function __construct_ThrowsExceptionIfRouteAndUrlAreProvided()
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_BOTH_URL_AND_ROUTE_VARIABLES_ARE_PROVIDED);

        // Execute
        $this->setUpSut([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE, static::THE_URL_VARIABLE_KEY => static::A_URL]);
    }

    /**
     * @test
     */
    public function __construct_SetsTemplate()
    {
        // Execute
        $this->setUpSut();

        // Assert
        $this->assertEquals(static::ANCHOR_TEMPLATE, $this->sut->getTemplate());
    }

    /**
     * @test
     */
    public function __construct_AppliesADefaultClass()
    {
        // Execute
        $this->setUpSut();

        // Assert
        $this->assertEquals(static::GOVUK_LINK_CLASS, $this->sut->getVariable(static::THE_CLASS_VARIABLE_KEY));
    }

    /**
     * @test
     */
    public function __construct_OverridesDefaultClass_WhenCustomClassIsProvided()
    {
        // Execute
        $this->setUpSut([static::THE_CLASS_VARIABLE_KEY => static::A_CUSTOM_CLASS]);

        // Assert
        $this->assertEquals(static::A_CUSTOM_CLASS, $this->sut->getVariable(static::THE_CLASS_VARIABLE_KEY));
    }

    /**
     * @test
     */
    public function setVariable_isCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setVariable']);
    }

    /**
     * @test
     * @depends setVariable_isCallable
     */
    public function setVariable_ThrowsExceptionIfRouteIsProvided_WhileUrlIsAlreadySet()
    {
        // Setup
        $this->setUpSut([static::THE_URL_VARIABLE_KEY => static::A_URL]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_SETTING_ROUTE_WHILE_URL_IS_ALREADY_SET);

        // Execute
        $this->sut->setVariable(static::THE_ROUTE_VARIABLE_KEY, static::A_ROUTE);
    }

    /**
     * @test
     * @depends setVariable_isCallable
     */
    public function setVariable_ThrowsExceptionIfUrlIsProvided_WhileRouteIsAlreadySet()
    {
        // Setup
        $this->setUpSut([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_SETTING_URL_WHILE_ROUTE_IS_ALREADY_SET);

        // Execute
        $this->sut->setVariable(static::THE_URL_VARIABLE_KEY, static::A_URL);
    }

    /**
     * @test
     */
    public function setVariables_isCallable()
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setVariables']);
    }

    /**
     * @test
     * @depends setVariables_isCallable
     */
    public function setVariables_ThrowsExceptionIfRouteAndUrlAreProvided()
    {
        // Setup
        $this->setUpSut();

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_BOTH_URL_AND_ROUTE_VARIABLES_ARE_PROVIDED);

        // Execute
        $this->sut->setVariables([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE, static::THE_URL_VARIABLE_KEY => static::A_URL]);
    }

    /**
     * @test
     * @depends setVariables_isCallable
     */
    public function setVariables_OverwritesRoute_WhenUrlIsAlreadySet()
    {
        // Setup
        $this->setUpSut([static::THE_URL_VARIABLE_KEY => static::A_URL]);

        // Execute
        $this->sut->setVariables([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE], static::OVERWRITE_EXISTING_VARIABLES);

        // Assert
        $this->assertEquals(static::A_ROUTE, $this->sut->getVariable(static::THE_ROUTE_VARIABLE_KEY));
    }

    /**
     * @test
     * @depends setVariables_isCallable
     */
    public function setVariables_ThrowsExceptionIfNotOverwriting_AndVariablesContainsRoute_WhileUrlIsAlreadySet()
    {
        // Setup
        $this->setUpSut([static::THE_URL_VARIABLE_KEY => static::A_URL]);

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXCEPTION_MESSAGE_WHEN_SETTING_ROUTE_WHILE_URL_IS_ALREADY_SET);

        // Execute
        $this->sut->setVariables([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE]);
    }

    /**
     * @test
     * @depends setVariables_isCallable
     */
    public function setVariables_OverwritesUrl_WhenRouteIsAlreadySet()
    {
        // Setup
        $this->setUpSut([static::THE_ROUTE_VARIABLE_KEY => static::A_ROUTE]);

        // Execute
        $this->sut->setVariables([static::THE_URL_VARIABLE_KEY => static::A_URL], static::OVERWRITE_EXISTING_VARIABLES);

        // Assert
        $this->assertEquals(static::A_URL, $this->sut->getVariable(static::THE_URL_VARIABLE_KEY));
    }

    /**
     * @test
     * @depends setVariables_isCallable
     */
    public function setVariables_ThrowsExceptionIfNotOverwriting_AndVariablesContainsUrl_WhileRouteIsAlreadySet()
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
     * @param mixed ...$args
     * @return AnchorViewModel
     */
    protected function setUpSut(...$args): AnchorViewModel
    {
        return $this->sut = new AnchorViewModel(...$args);
    }
}
