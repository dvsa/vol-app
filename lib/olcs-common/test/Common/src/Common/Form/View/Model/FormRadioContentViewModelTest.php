<?php

declare(strict_types=1);

namespace CommonTest\Form\View\Model;

use Common\Test\MockeryTestCase;
use Common\Form\View\Model\FormRadioContentViewModel;

/**
 * @see FormRadioContentViewModel
 */
class FormRadioContentViewModelTest extends MockeryTestCase
{
    protected const ID_VARIABLE_KEY = 'id';

    protected const ID_VARIABLE_VALUE = 'ID VARIABLE VALUE';

    protected const VALUE_OPTION_ID = 'VALUE OPTION ID';

    protected const CONTENT_CONTAINER_ID = 'VALUE OPTION ID_content';

    protected const CONTENT_CONTAINER_ID_FOR_VALUE_OPTION_WITHOUT_ID = '_content';

    protected const A_VALUE_OPTION_WITH_ID = ['attributes' => ['id' => self::VALUE_OPTION_ID], 'conditional_content' => ''];

    protected const A_VALUE_OPTION_WITHOUT_ID = ['conditional_content' => ''];

    protected const CONDITIONAL_CONTENT = 'CONDITIONAL CONTENT';

    protected const CONDITIONAL_CONTENT_VARIABLE_KEY = 'content';

    protected const A_VALUE_OPTION_WITH_CONDITIONAL_CONTENT = ['conditional_content' => self::CONDITIONAL_CONTENT];

    protected const VIEW_TEMPLATE = 'partials/form/radio-content';

    protected const CLASS_VARIABLE_KEY = 'class';

    protected const DEFAULT_ELEMENT_CLASS_STRING = 'govuk-radios__conditional govuk-body';

    protected const A_VALUE_OPTION_WITH_CUSTOM_CLASS_STRING = ['attributes' => ['id' => self::VALUE_OPTION_ID, 'class' => 'foo bar'], 'conditional_content' => ''];

    protected const A_VALUE_OPTION_WITH_CUSTOM_CLASS_STRING_WITH_DUPLICATES = ['attributes' => ['id' => self::VALUE_OPTION_ID, 'class' => 'foo bar foo'], 'conditional_content' => ''];

    protected const A_VALUE_OPTION_WITH_CUSTOM_CLASS_ARRAY = ['attributes' => ['id' => self::VALUE_OPTION_ID, 'class' => ['foo', 'bar']], 'conditional_content' => ''];

    protected const ELEMENT_CLASS_STRING_WITH_CUSTOM_CLASS = self::DEFAULT_ELEMENT_CLASS_STRING . ' foo bar';

    protected const A_VALUE_OPTION_WITH_CUSTOM_CLASS_ARRAY_WITH_DUPLICATES = ['attributes' => ['id' => self::VALUE_OPTION_ID, 'class' => ['foo', 'bar', 'foo']], 'conditional_content' => ''];


    /**
     * @var FormRadioContentViewModel|null
     */
    protected $sut;

    /**
     * @test
     */
    public function constructInitialisesIdVariable(): void
    {
        // Setup
        $this->setUpSut(static::A_VALUE_OPTION_WITH_ID);

        // Assert
        $this->assertEquals(static::CONTENT_CONTAINER_ID, $this->sut->getVariable(static::ID_VARIABLE_KEY));
    }

    /**
     * @test
     * @depends constructInitialisesIdVariable
     */
    public function constructInitialisesIdVariableWhenNoIdIsSetOnValueOption(): void
    {
        // Setup
        $this->setUpSut(static::A_VALUE_OPTION_WITHOUT_ID);

        // Assert
        $this->assertEquals(static::CONTENT_CONTAINER_ID_FOR_VALUE_OPTION_WITHOUT_ID, $this->sut->getVariable(static::ID_VARIABLE_KEY));
    }

    /**
     * @test
     */
    public function constructConditionalContentVariable(): void
    {
        // Setup
        $this->setUpSut(static::A_VALUE_OPTION_WITH_CONDITIONAL_CONTENT);

        // Assert
        $this->assertEquals(static::CONDITIONAL_CONTENT, $this->sut->getVariable(static::CONDITIONAL_CONTENT_VARIABLE_KEY));
    }

    /**
     * @test
     */
    public function constructHasTemplate(): void
    {
        // Setup
        $this->setUpSut(static::A_VALUE_OPTION_WITH_ID);

        // Assert
        $this->assertEquals(static::VIEW_TEMPLATE, $this->sut->getTemplate());
    }

    /**
     * @test
     */
    public function constructSetClassVariableWithDefaultClasses(): void
    {
        // Setup
        $this->setUpSut(static::A_VALUE_OPTION_WITH_ID);

        // Assert
        $this->assertEquals(static::DEFAULT_ELEMENT_CLASS_STRING, $this->sut->getVariable(static::CLASS_VARIABLE_KEY));
    }

    /**
     * @test
     * @depends constructSetClassVariableWithDefaultClasses
     */
    public function constructSetClassVariableWithCustomClassesFromString(): void
    {
        // Setup
        $this->setUpSut(static::A_VALUE_OPTION_WITH_CUSTOM_CLASS_STRING);

        // Assert
        $this->assertEquals(static::ELEMENT_CLASS_STRING_WITH_CUSTOM_CLASS, $this->sut->getVariable(static::CLASS_VARIABLE_KEY));
    }

    /**
     * @test
     * @depends constructSetClassVariableWithCustomClassesFromString
     */
    public function constructSetClassVariableWithCustomClassesFromStringAndEliminatesDuplicates(): void
    {
        // Setup
        $this->setUpSut(static::A_VALUE_OPTION_WITH_CUSTOM_CLASS_STRING_WITH_DUPLICATES);

        // Assert
        $this->assertEquals(static::ELEMENT_CLASS_STRING_WITH_CUSTOM_CLASS, $this->sut->getVariable(static::CLASS_VARIABLE_KEY));
    }

    /**
     * @test
     * @depends constructSetClassVariableWithDefaultClasses
     */
    public function constructSetClassVariableWithCustomClassesFromArray(): void
    {
        // Setup
        $this->setUpSut(static::A_VALUE_OPTION_WITH_CUSTOM_CLASS_ARRAY);

        // Assert
        $this->assertEquals(static::ELEMENT_CLASS_STRING_WITH_CUSTOM_CLASS, $this->sut->getVariable(static::CLASS_VARIABLE_KEY));
    }

    /**
     * @test
     * @depends constructSetClassVariableWithCustomClassesFromArray
     */
    public function constructSetClassVariableWithCustomClassesFromArrayAndEliminatesDuplicates(): void
    {
        // Setup
        $this->setUpSut(static::A_VALUE_OPTION_WITH_CUSTOM_CLASS_ARRAY_WITH_DUPLICATES);

        // Assert
        $this->assertEquals(static::ELEMENT_CLASS_STRING_WITH_CUSTOM_CLASS, $this->sut->getVariable(static::CLASS_VARIABLE_KEY));
    }

    protected function setUpSut(...$args): void
    {
        $this->sut = new FormRadioContentViewModel(...$args);
    }
}
