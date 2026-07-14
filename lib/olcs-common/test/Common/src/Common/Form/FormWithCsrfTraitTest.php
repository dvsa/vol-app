<?php

declare(strict_types=1);

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
final class FormWithCsrfTraitTest extends MockeryTestCase
{
    protected const array EMPTY_ARRAY_VALUE = [];

    protected const string INVALID_CSRF_VALUE = 'AN INVALID CSRF VALUE';

    protected const string CSRF_KEY = 'security';

    protected const string EMPTY_STRING_VALUE = '';

    /**
     * @var FormWithCsrfInterface|Form
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function getCsrfElementIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getCsrfElement']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getCsrfElementIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getCsrfElementReturnsACsrfElement(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertInstanceOf(Csrf::class, $this->sut->getCsrfElement());
    }

    #[\PHPUnit\Framework\Attributes\Depends('getCsrfElementReturnsACsrfElement')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getCsrfElementReturnsACsrfElementWithAName(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertEquals(static::CSRF_KEY, $this->sut->getCsrfElement()->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getCsrfInputIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getCsrfInput']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getCsrfInputIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getCsrfInputReturnsInstanceOfInput(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getCsrfInput();

        // Assert
        $this->assertInstanceOf(InputInterface::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getCsrfInputReturnsInstanceOfInput')]
    #[\PHPUnit\Framework\Attributes\Test]
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

    #[\PHPUnit\Framework\Attributes\Depends('getCsrfInputReturnsInstanceOfInput')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getCsrfInputReturnsInstanceOfInputThatAcceptsAValidValue(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $csrfValidator = $this->sut->getCsrfInput()->getValidatorChain()->getValidators()[0]['instance'];
        $this->assertInstanceOf(\Laminas\Validator\Csrf::class, $csrfValidator);
        $this->sut->setData([static::CSRF_KEY => $csrfValidator->getHash()]);
        $this->sut->isValid();

        // Assert
        $this->assertNull($this->sut->getMessages()[static::CSRF_KEY] ?? null);
    }

    public static function csrfInvalidValueDataProvider(): \Iterator
    {
        yield 'non-empty invalid csrf value' => [static::INVALID_CSRF_VALUE];
        yield 'empty csrf value - string' => [static::EMPTY_STRING_VALUE];
        yield 'empty csrf value - null' => [null];
    }

    #[\PHPUnit\Framework\Attributes\Depends('getCsrfInputReturnsInstanceOfInput')]
    #[\PHPUnit\Framework\Attributes\DataProvider('csrfInvalidValueDataProvider')]
    #[\PHPUnit\Framework\Attributes\Test]
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
