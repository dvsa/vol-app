<?php

declare(strict_types=1);

namespace CommonTest\Test\Form;

use Common\Test\MockeryTestCase;
use Common\Test\Form\FormValidatorBuilder;
use Common\Form\FormValidator;
use Common\Form\Form;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element;

/**
 * @see FormValidatorBuilder
 */
class FormValidationBuilderTest extends MockeryTestCase
{
    protected const CSRF_FIELD_NAME = 'CSRF_FIELD_NAME';

    /**
     * @var FormValidatorBuilder
     */
    protected $sut;

    /**
     * @test
     */
    public function aValidatorIsCallable(): void
    {
        // Assert
        $this->assertIsCallable(static fn(): \Common\Test\Form\FormValidatorBuilder => \Common\Test\Form\FormValidatorBuilder::aValidator());
    }

    /**
     * @test
     * @depends aValidatorIsCallable
     */
    public function aValidatorReturnsInstanceOfSelf(): void
    {
        // Execute
        $result = FormValidatorBuilder::aValidator();

        // Assert
        $this->assertInstanceOf(FormValidatorBuilder::class, $result);
    }

    /**
     * @test
     */
    public function populateCsrfValidationBeforeValidatingIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn(): \Common\Test\Form\FormValidatorBuilder => $this->sut->populateCsrfDataBeforeValidating());
    }

    /**
     * @test
     * @depends populateCsrfValidationBeforeValidatingIsCallable
     */
    public function populateCsrfValidationBeforeValidatingReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->populateCsrfDataBeforeValidating();

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @test
     */
    public function buildIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn(): \Common\Form\FormValidator => $this->sut->build());
    }

    /**
     * @test
     * @depends buildIsCallable
     */
    public function buildReturnsInstanceOfFormValidator(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->build();

        // Assert
        $this->assertInstanceOf(FormValidator::class, $result);
    }

    /**
     * @test
     * @depends buildReturnsInstanceOfFormValidator
     */
    public function buildAllowsCsrfValidation(): void
    {
        // Setup
        $this->setUpSut();
        $form = $this->formWithInvalidCsrf();

        // Execute
        $result = $this->sut->build()->isValid($form);

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @test
     * @depends buildReturnsInstanceOfFormValidator
     * @depends populateCsrfValidationBeforeValidatingReturnsSelf
     */
    public function buildDisablesCsrfForTopLevelCsrfFormElement(): void
    {
        // Setup
        $this->setUpSut();
        $form = $this->formWithInvalidCsrf();

        // Execute
        $result = $this->sut->populateCsrfDataBeforeValidating()->build()->isValid($form);

        // Assert
        $this->assertTrue($result);
    }

    protected function setUpSut(): void
    {
        $this->sut = new FormValidatorBuilder();
    }

    protected function formWithElement(Element $element): Form
    {
        $instance = new Form();
        $instance->add($element);
        $instance->setData([]);
        return $instance;
    }

    protected function formWithInvalidCsrf(): Form
    {
        return $this->formWithElement(new Csrf(static::CSRF_FIELD_NAME));
    }
}
