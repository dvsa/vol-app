<?php

namespace CommonTest\Common\Form;

use Common\Test\MockeryTestCase;
use Laminas\Form\Element\Csrf;
use Common\Form\FormWithCsrfInterface;
use Common\Form\FormWithCsrfTrait;
use Laminas\InputFilter\InputInterface;
use Common\Form\Form;

/**
 * @see FormWithCsrfTrait
 */
class FormWithCsrfTraitTest extends MockeryTestCase
{
    protected const EMPTY_ARRAY_VALUE = [];

    protected const INVALID_CSRF_VALUE = 'AN INVALID CSRF VALUE';

    protected const CSRF_KEY = 'security';

    protected const EMPTY_STRING_VALUE = '';

    /**
     * @var FormWithCsrfInterface|Form
     */
    protected $sut;

    /**
     * @test
     */
    public function getCsrfElementIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getCsrfElement']);
    }

    /**
     * @test
     * @depends getCsrfElementIsCallable
     */
    public function getCsrfElementReturnsACsrfElement(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertInstanceOf(Csrf::class, $this->sut->getCsrfElement());
    }

    /**
     * @test
     * @depends getCsrfElementReturnsACsrfElement
     */
    public function getCsrfElementReturnsACsrfElementWithAName(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertEquals(static::CSRF_KEY, $this->sut->getCsrfElement()->getName());
    }

    /**
     * @test
     */
    public function getCsrfInputIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getCsrfInput']);
    }

    /**
     * @test
     * @depends getCsrfInputIsCallable
     */
    public function getCsrfInputReturnsInstanceOfInput(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getCsrfInput();

        // Assert
        $this->assertInstanceOf(InputInterface::class, $result);
    }

    /**
     * @test
     * @depends getCsrfInputReturnsInstanceOfInput
     */
    public function getCsrfInputReturnsInstanceOfInputThatIsRequired(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setData(static::EMPTY_ARRAY_VALUE);
        $this->sut->isValid();

        // Assert
        $this->assertNotNull($this->sut->getMessages()[static::CSRF_KEY] ?? null);
    }

    /**
     * @test
     * @depends getCsrfInputReturnsInstanceOfInput
     */
    public function getCsrfInputReturnsInstanceOfInputThatAcceptsAValidValue(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $csrfValidator = $this->sut->getCsrfInput()->getValidatorChain()->getValidators()[0]['instance'];
        assert($csrfValidator instanceof \Laminas\Validator\Csrf);
        $this->sut->setData([static::CSRF_KEY => $csrfValidator->getHash()]);
        $this->sut->isValid();

        // Assert
        $this->assertNull($this->sut->getMessages()[static::CSRF_KEY] ?? null);
    }

    public function csrfInvalidValueDataProvider(): array
    {
        return [
            'non-empty invalid csrf value' => [static::INVALID_CSRF_VALUE],
            'empty csrf value - string' => [static::EMPTY_STRING_VALUE],
            'empty csrf value - null' => [null],
        ];
    }

    /**
     * @test
     * @depends getCsrfInputReturnsInstanceOfInput
     * @dataProvider csrfInvalidValueDataProvider
     */
    public function getCsrfInputReturnsInstanceOfInputThatRejectsAnInvalidValue(mixed $value): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setData([static::CSRF_KEY => $value]);
        $this->sut->isValid();

        // Assert
        $this->assertNotNull($this->sut->getMessages()[static::CSRF_KEY] ?? null);
    }

    protected function setUpSut(): void
    {
        $this->sut = new FormWithCsrfStub();
    }
}
