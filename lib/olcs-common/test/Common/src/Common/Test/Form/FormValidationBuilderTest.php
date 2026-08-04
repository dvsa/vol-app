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
final class FormValidationBuilderTest extends MockeryTestCase
{
    protected const string CSRF_FIELD_NAME = 'CSRF_FIELD_NAME';

    /**
     * @var FormValidatorBuilder
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function aValidatorIsCallable(): void
    {
        // Assert
        $this->assertIsCallable(static fn(): \Common\Test\Form\FormValidatorBuilder => \Common\Test\Form\FormValidatorBuilder::aValidator());
    }

    #[\PHPUnit\Framework\Attributes\Depends('aValidatorIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function aValidatorReturnsInstanceOfSelf(): void
    {
        // Execute
        $result = FormValidatorBuilder::aValidator();

        // Assert
        $this->assertInstanceOf(FormValidatorBuilder::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function populateCsrfValidationBeforeValidatingIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn(): \Common\Test\Form\FormValidatorBuilder => $this->sut->populateCsrfDataBeforeValidating());
    }

    #[\PHPUnit\Framework\Attributes\Depends('populateCsrfValidationBeforeValidatingIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function populateCsrfValidationBeforeValidatingReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->populateCsrfDataBeforeValidating();

        // Assert
        $this->assertSame($this->sut, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function buildIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable(fn(): \Common\Form\FormValidator => $this->sut->build());
    }

    #[\PHPUnit\Framework\Attributes\Depends('buildIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function buildReturnsInstanceOfFormValidator(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->build();

        // Assert
        $this->assertInstanceOf(FormValidator::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('buildReturnsInstanceOfFormValidator')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Depends('buildReturnsInstanceOfFormValidator')]
    #[\PHPUnit\Framework\Attributes\Depends('populateCsrfValidationBeforeValidatingReturnsSelf')]
    #[\PHPUnit\Framework\Attributes\Test]
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
