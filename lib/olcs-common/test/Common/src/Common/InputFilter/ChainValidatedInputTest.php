<?php

declare(strict_types=1);

namespace CommonTest\InputFilter;

use Common\Test\MockeryTestCase;
use Common\InputFilter\ChainValidatedInput;
use InvalidArgumentException;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorInterface;
use Laminas\Filter\FilterChain;
use Mockery as m;
use Laminas\InputFilter\Input;

/**
 * @see ChainValidatedInput
 */
final class ChainValidatedInputTest extends MockeryTestCase
{
    protected const string AN_INPUT_NAME = 'AN INPUT NAME';

    protected const string A_CUSTOM_INPUT_NAME = 'A CUSTOM INPUT NAME';

    protected const string AN_ALTERNATIVE_INPUT_NAME = 'AN ALTERNATIVE INPUT NAME';

    protected const string EXPECTED_STRING_EXCEPTION_MESSAGE = 'Expected string';

    protected const int AN_INT = 0;

    protected const bool REQUIRED = true;

    protected const bool NOT_REQUIRED = false;

    protected const string EXPECTED_BOOL_EXCEPTION_MESSAGE = 'Expected bool';

    protected const bool ALLOW_EMPTY = true;

    protected const bool DONT_ALLOW_EMPTY = false;

    protected const bool BREAK_ON_FAILURE = true;

    protected const bool DONT_BREAK_ON_FAILURE = false;

    protected const string A_CUSTOM_ERROR_MESSAGE = 'AN ERROR MESSAGE';

    protected const array A_MESSAGES_ARRAY_CONTAINING_A_CUSTOM_ERROR_MESSAGE = [self::A_CUSTOM_ERROR_MESSAGE];

    protected const null NO_ERROR_MESSAGE = null;

    protected const null THE_DEFAULT_INPUT_VALUE = null;

    protected const string A_RAW_INPUT_VALUE = 'A RAW INPUT VALUE';

    protected const string A_SECOND_RAW_INPUT_VALUE = 'A SECOND RAW INPUT VALUE';

    protected const string A_FILTERED_INPUT_VALUE = 'A FILTERED INPUT VALUE';

    protected const array EMPTY_VALIDATION_CONTEXT = [];

    protected const bool VALID = true;

    protected const bool NOT_VALID = false;

    protected const array MESSAGES_FOR_A_VALID_INPUT = [];

    protected const array MESSAGES_FOR_AN_INVALID_INPUT = ['A VALIDATOR KEY' => 'AN VALIDATION MESSAGE'];

    protected const null THE_DEFAULT_ERROR_MESSAGE = null;

    protected const string A_STRING_SUFFIX = 'A STRING SUFFIX';

    protected const string A_SECOND_STRING_SUFFIX = 'A SECOND STRING SUFFIX';

    protected const string AN_EMPTY_RAW_INPUT_VALUE = '';

    /**
     * @var ChainValidatedInput|null
     */
    protected $sut;

    #[\PHPUnit\Framework\Attributes\Test]
    public function getNameIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getName']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getNameIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getNameReturnsTheNameOfAnInput(): void
    {
        // Setup
        $this->setUpSut(static::AN_INPUT_NAME);

        // Assert
        $this->assertSame(static::AN_INPUT_NAME, $this->sut->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setNameIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setName']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setNameIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setNameReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertSame($this->sut, $this->sut->setName(static::AN_INPUT_NAME));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setNameIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('getNameReturnsTheNameOfAnInput')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setNameSetsTheName(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setName(static::AN_ALTERNATIVE_INPUT_NAME);

        // Assert
        $this->assertSame(static::AN_ALTERNATIVE_INPUT_NAME, $this->sut->getName());
    }

    #[\PHPUnit\Framework\Attributes\Depends('setNameIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('getNameReturnsTheNameOfAnInput')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setNameThrowsInvalidArgumentExceptionIfNotPassedAString(): void
    {
        // Setup
        $this->setUpSut();

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXPECTED_STRING_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->setName(static::AN_INT);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function isRequiredIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'isRequired']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('isRequiredIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function isRequiredReturnsABoolean(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->isRequired();

        // Assert
        $this->assertIsBool($result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('isRequiredReturnsABoolean')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function isRequiredReturnsTrueByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->isRequired();

        // Assert
        $this->assertTrue($result);
    }


    #[\PHPUnit\Framework\Attributes\Test]
    public function setRequiredIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setRequired']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setRequiredIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setRequiredReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->setRequired(static::REQUIRED);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setRequiredIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setRequiredThrowsInvalidArgumentExceptionIfProvidedNonBoolean(): void
    {
        // Setup
        $this->setUpSut();

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXPECTED_BOOL_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->setRequired(static::AN_INT);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setRequiredIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('isRequiredReturnsABoolean')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setRequiredSetsAnInputAsRequired(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setRequired(static::REQUIRED);

        // Assert
        $this->assertSame(static::REQUIRED, $this->sut->isRequired());
    }

    #[\PHPUnit\Framework\Attributes\Depends('setRequiredIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('isRequiredReturnsABoolean')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setRequiredSetsAnInputAsNotRequired(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setRequired(static::NOT_REQUIRED);

        // Assert
        $this->assertSame(static::NOT_REQUIRED, $this->sut->isRequired());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getValidatorChainIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getValidatorChain']);
    }


    #[\PHPUnit\Framework\Attributes\Depends('getValidatorChainIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getValidatorChainReturnsAValidatorChain(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertInstanceOf(ValidatorChain::class, $this->sut->getValidatorChain());
    }

    #[\PHPUnit\Framework\Attributes\Depends('getValidatorChainReturnsAValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getValidatorChainThatIsEmptyByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getValidatorChain();
        $this->assertInstanceOf(\Laminas\Validator\ValidatorChain::class, $result);

        // Assert
        $this->assertEmpty($result->getValidators());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setValidatorChainIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setValidatorChain']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setValidatorChainIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setValidatorChainReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->setValidatorChain($this->emptyValidatorChain());

        // Assert
        $this->assertSame($this->sut, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setValidatorChainIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('getValidatorChainIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setValidatorChainSetsTheValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $newValidatorChain = $this->emptyValidatorChain();

        // Execute
        $this->sut->setValidatorChain($newValidatorChain);

        // Assert
        $this->assertSame($newValidatorChain, $this->sut->getValidatorChain());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function allowEmptyIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'allowEmpty']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('getValidatorChainReturnsAValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $validatorChain = $this->sut->getValidatorChain();
        $this->assertInstanceOf(\Laminas\Validator\ValidatorChain::class, $validatorChain);
        $validatorChain->attach(new NotEmpty());

        // Assert
        $this->assertSame(static::ALLOW_EMPTY, $this->sut->allowEmpty());
    }

    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setValidatorChainSetsTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValidatorChain($this->emptyValidatorChain());

        // Assert
        $this->assertSame(static::DONT_ALLOW_EMPTY, $this->sut->allowEmpty());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setAllowEmptyIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setAllowEmpty']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAllowEmptyIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAllowEmptyReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->setAllowEmpty(static::ALLOW_EMPTY);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAllowEmptyIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAllowEmptyThrowsInvalidArgumentExceptionIfNotPassedABool(): void
    {
        // Setup
        $this->setUpSut();

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXPECTED_BOOL_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->setAllowEmpty(static::AN_INT);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAllowEmptyIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAllowEmptyWhenPassedFalseAndNotEmptyValidatorIsNotPresentInTheValidatorChainAddsNotEmptyValidatorToTheValidatorChain(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setAllowEmpty(static::DONT_ALLOW_EMPTY);

        // Assert
        $this->assertInstanceOf(\Laminas\Validator\NotEmpty::class, $this->getNotEmptyValidatorFromValidatorChain($this->sut->getValidatorChain()));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAllowEmptyIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('setValidatorChainSetsTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAllowEmptyWhenPassedFalseAndNotEmptyValidatorIsPresentInTheValidatorChainLeavesNotEmptyValidator(): void
    {
        // Setup
        $this->setUpSut();
        $notEmptyValidator = new NotEmpty();
        $this->sut->setValidatorChain($this->validatorChainWithValidator($notEmptyValidator));

        // Execute
        $this->sut->setAllowEmpty(static::DONT_ALLOW_EMPTY);

        // Assert
        $this->assertSame($notEmptyValidator, $this->getNotEmptyValidatorFromValidatorChain($this->sut->getValidatorChain()));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAllowEmptyIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('setValidatorChainSetsTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAllowEmptyWhenPassedTrueAndNotEmptyValidatorIsPresentInTheValidatorChainRemovesNotEmptyValidatorFromTheValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $notEmptyValidator = new NotEmpty();
        $this->sut->setValidatorChain($this->validatorChainWithValidator($notEmptyValidator));

        // Execute
        $this->sut->setAllowEmpty(static::ALLOW_EMPTY);

        // Assert
        $this->assertNotInstanceOf(\Laminas\Validator\NotEmpty::class, $this->getNotEmptyValidatorFromValidatorChain($this->sut->getValidatorChain()));
    }

    #[\PHPUnit\Framework\Attributes\Depends('setAllowEmptyIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('setValidatorChainSetsTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setAllowEmptyWhenPassedTrueAndNotEmptyValidatorIsNotPresentInTheValidatorChainDoesNotAddNotEmptyValidator(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValidatorChain($this->emptyValidatorChain());

        // Execute
        $this->sut->setAllowEmpty(static::ALLOW_EMPTY);

        // Assert
        $this->assertNotInstanceOf(\Laminas\Validator\NotEmpty::class, $this->getNotEmptyValidatorFromValidatorChain($this->sut->getValidatorChain()));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function breakOnFailureIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'breakOnFailure']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('breakOnFailureIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function breakOnFailureReturnsFalseByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertEquals(static::DONT_BREAK_ON_FAILURE, $this->sut->breakOnFailure());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setBreakOnFailureIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setBreakOnFailure']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setBreakOnFailureIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setBreakOnFailureThrowsInvalidArgumentExceptionIfNotPassedABool(): void
    {
        // Setup
        $this->setUpSut();

        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(static::EXPECTED_BOOL_EXCEPTION_MESSAGE);

        // Execute
        $this->sut->setBreakOnFailure(static::AN_INT);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setBreakOnFailureIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('breakOnFailureIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setBreakOnFailureSetsTheBreakOnFailureFlagToTrue(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setBreakOnFailure(static::BREAK_ON_FAILURE);

        // Assert
        $this->assertSame(static::BREAK_ON_FAILURE, $this->sut->breakOnFailure());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getErrorMessageIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getErrorMessage']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getErrorMessageIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getErrorMessageReturnsNullByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getErrorMessage();

        // Assert
        $this->assertSame(static::THE_DEFAULT_ERROR_MESSAGE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setErrorMessageIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setErrorMessage']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getErrorMessageIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setErrorMessageIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setErrorMessageWhenProvidedAStringSetsTheString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setErrorMessage(static::A_CUSTOM_ERROR_MESSAGE);

        // Assert
        $this->assertSame(static::A_CUSTOM_ERROR_MESSAGE, $this->sut->getErrorMessage());
    }

    #[\PHPUnit\Framework\Attributes\Depends('getErrorMessageIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setErrorMessageIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setErrorMessageWhenProvidedNullSetsNull(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setErrorMessage(static::NO_ERROR_MESSAGE);

        // Assert
        $this->assertSame(static::NO_ERROR_MESSAGE, $this->sut->getErrorMessage());
    }

    #[\PHPUnit\Framework\Attributes\Depends('getErrorMessageIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setErrorMessageIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setErrorMessageWhenProvidedAnObjectSetsStringRepresentationOfAnObject(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setErrorMessage($this->errorMessageObjectThatCastsToAString(static::A_CUSTOM_ERROR_MESSAGE));

        // Assert
        $this->assertSame(static::A_CUSTOM_ERROR_MESSAGE, $this->sut->getErrorMessage());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getFilterChainIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getFilterChain']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getFilterChainReturnsAFilterChain(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getFilterChain();

        // Assert
        $this->assertInstanceOf(FilterChain::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setFilterChainIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setFilterChain']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setFilterChainIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setFilterChainReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();
        $filterChain = new FilterChain();

        // Execute
        $result = $this->sut->setFilterChain($filterChain);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setFilterChainIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('getFilterChainReturnsAFilterChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setFilterChainSetsAFilterChain(): void
    {
        // Setup
        $this->setUpSut();
        $filterChain = new FilterChain();

        // Execute
        $this->sut->setFilterChain($filterChain);

        // Assert
        $this->assertSame($filterChain, $this->sut->getFilterChain());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getRawValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getRawValue']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getRawValueIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getRawValueReturnsNullByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getRawValue();

        // Assert
        $this->assertSame(static::THE_DEFAULT_INPUT_VALUE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getRawValueIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setFilterChainSetsAFilterChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getRawValueReturnsTheInputValueWithoutTheFilterChainApplied(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setFilterChain($this->filterChainThatConvertsAllValuesTo(static::A_RAW_INPUT_VALUE));

        // Execute
        $result = $this->sut->getRawValue();

        // Assert
        $this->assertSame(static::THE_DEFAULT_INPUT_VALUE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getValue']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getValueReturnsNullByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getValue();

        // Assert
        $this->assertSame(static::THE_DEFAULT_INPUT_VALUE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getValueIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setFilterChainSetsAFilterChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getValueReturnsTheInputValueWithTheFilterChainApplied(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setFilterChain($this->filterChainThatConvertsAllValuesTo(static::A_FILTERED_INPUT_VALUE));

        // Execute
        $result = $this->sut->getValue();

        // Assert
        $this->assertSame(static::A_FILTERED_INPUT_VALUE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setValue']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setValueIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setValueReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->setValue(static::A_RAW_INPUT_VALUE);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setValueIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('getRawValueReturnsTheInputValueWithoutTheFilterChainApplied')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function setValueSetsAValue(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setValue(static::A_RAW_INPUT_VALUE);
        $result = $this->sut->getRawValue();

        // Assert
        $this->assertSame(static::A_RAW_INPUT_VALUE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function isValidIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'isValid']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function isValidReturnsBoolean(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsBool($this->sut->isValid());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function isValidReturnsTrueByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertSame(static::VALID, $this->sut->isValid());
    }

    #[\PHPUnit\Framework\Attributes\Depends('isValidReturnsBoolean')]
    #[\PHPUnit\Framework\Attributes\Depends('setValidatorChainSetsTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('setValueSetsAValue')]
    #[\PHPUnit\Framework\Attributes\Depends('setFilterChainSetsAFilterChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function isValidProxiesToValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $mockValidatorChain = m::mock(ValidatorChain::class)->shouldIgnoreMissing();
        $this->sut->setValidatorChain($mockValidatorChain);
        $this->sut->setValue(static::A_RAW_INPUT_VALUE);
        $this->sut->setFilterChain($this->filterChainThatConvertsAllValuesTo(static::A_FILTERED_INPUT_VALUE));

        // Expect
        $mockValidatorChain->expects('isValid')->withArgs(function ($value, $context) {
            $this->assertSame(static::A_FILTERED_INPUT_VALUE, $value);
            $this->assertSame(static::EMPTY_VALIDATION_CONTEXT, $context);
            return true;
        })->andReturn(static::NOT_VALID);

        // Execute
        $result = $this->sut->isValid(static::EMPTY_VALIDATION_CONTEXT);

        // Assert
        $this->assertSame(static::NOT_VALID, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getMessagesIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getMessages']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getMessagesIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getMessagesReturnsAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getMessages();

        // Assert
        $this->assertIsArray($result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getMessagesReturnsAnArray')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getMessagesReturnsAnEmptyArrayByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getMessages();

        // Assert
        $this->assertSame(static::MESSAGES_FOR_A_VALID_INPUT, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getMessagesReturnsAnArray')]
    #[\PHPUnit\Framework\Attributes\Depends('setValidatorChainSetsTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getMessagesReturnsMessagesFromValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $mockValidatorChain = m::mock(ValidatorChain::class)->shouldIgnoreMissing();
        $mockValidatorChain->allows('getMessages')->andReturn(static::MESSAGES_FOR_AN_INVALID_INPUT);
        $this->sut->setValidatorChain($mockValidatorChain);

        // Execute
        $result = $this->sut->getMessages();

        // Assert
        $this->assertSame(static::MESSAGES_FOR_AN_INVALID_INPUT, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getMessagesReturnsAnArray')]
    #[\PHPUnit\Framework\Attributes\Depends('setErrorMessageWhenProvidedAStringSetsTheString')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getMessagesReturnsAnArrayWithCustomErrorMessage(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setErrorMessage(static::A_CUSTOM_ERROR_MESSAGE);

        // Execute
        $result = $this->sut->getMessages();

        // Assert
        $this->assertSame(static::A_MESSAGES_ARRAY_CONTAINING_A_CUSTOM_ERROR_MESSAGE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getMessagesReturnsAnArrayWithCustomErrorMessage')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function getMessagesReturnsAnArrayWithCustomErrorMessageWhenThereAreValidationMessagesFromTheChain(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValidatorChain($this->validatorChainWithErrorMessage());
        $this->sut->setErrorMessage(static::A_CUSTOM_ERROR_MESSAGE);

        // Execute
        $result = $this->sut->getMessages();

        // Assert
        $this->assertSame(static::A_MESSAGES_ARRAY_CONTAINING_A_CUSTOM_ERROR_MESSAGE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'merge']);
    }

    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();
        $inputToMerge = new ChainValidatedInput(static::AN_INPUT_NAME);

        // Execute
        $result = $this->sut->merge($inputToMerge);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setBreakOnFailureSetsTheBreakOnFailureFlagToTrue')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeSetsBreakOnFailure(): void
    {
        // Setup
        $this->setUpSut();
        $inputToMerge = new ChainValidatedInput(static::AN_INPUT_NAME);
        $inputToMerge->setBreakOnFailure(static::BREAK_ON_FAILURE);

        // Execute
        $this->sut->merge($inputToMerge);
        $result = $this->sut->breakOnFailure();

        // Assert
        $this->assertSame(static::BREAK_ON_FAILURE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setErrorMessageWhenProvidedAStringSetsTheString')]
    #[\PHPUnit\Framework\Attributes\Depends('getErrorMessageIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeSetsErrorMessage(): void
    {
        // Setup
        $this->setUpSut();
        $inputToMerge = new ChainValidatedInput(static::AN_INPUT_NAME);
        $inputToMerge->setErrorMessage(static::A_CUSTOM_ERROR_MESSAGE);

        // Execute
        $this->sut->merge($inputToMerge);
        $result = $this->sut->getErrorMessage();

        // Assert
        $this->assertSame(static::A_CUSTOM_ERROR_MESSAGE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setNameSetsTheName')]
    #[\PHPUnit\Framework\Attributes\Depends('getNameReturnsTheNameOfAnInput')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeSetsName(): void
    {
        // Setup
        $this->setUpSut();
        $inputToMerge = new ChainValidatedInput(static::AN_INPUT_NAME);
        $inputToMerge->setName(static::A_CUSTOM_INPUT_NAME);

        // Execute
        $this->sut->merge($inputToMerge);
        $result = $this->sut->getName();

        // Assert
        $this->assertSame(static::A_CUSTOM_INPUT_NAME, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('isRequiredReturnsABoolean')]
    #[\PHPUnit\Framework\Attributes\Depends('setRequiredSetsAnInputAsNotRequired')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeSetsIsRequired(): void
    {
        // Setup
        $this->setUpSut();
        $inputToMerge = new ChainValidatedInput(static::AN_INPUT_NAME);
        $inputToMerge->setRequired(static::NOT_REQUIRED);

        // Execute
        $this->sut->merge($inputToMerge);
        $result = $this->sut->isRequired();

        // Assert
        $this->assertSame(static::NOT_REQUIRED, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('setValueSetsAValue')]
    #[\PHPUnit\Framework\Attributes\Depends('setFilterChainSetsAFilterChain')]
    #[\PHPUnit\Framework\Attributes\Depends('getRawValueReturnsTheInputValueWithoutTheFilterChainApplied')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeSetsValueToRawValueForInputsThatAreNotLaminas(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValue(static::A_RAW_INPUT_VALUE);
        $inputToMerge = new ChainValidatedInput(static::AN_INPUT_NAME);
        $inputToMerge->setValue(static::A_SECOND_RAW_INPUT_VALUE);
        $inputToMerge->setFilterChain($this->filterChainThatConvertsAllValuesTo(static::A_FILTERED_INPUT_VALUE));

        // Execute
        $this->sut->merge($inputToMerge);
        $result = $this->sut->getRawValue();

        // Assert
        $this->assertSame(static::A_SECOND_RAW_INPUT_VALUE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('getRawValueReturnsTheInputValueWithoutTheFilterChainApplied')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeSetsValueToRawValueForInputsThatAreLaminasAndHaveAValueSet(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValue(static::A_RAW_INPUT_VALUE);
        $inputToMerge = new Input(static::AN_INPUT_NAME);
        $inputToMerge->setValue(static::A_SECOND_RAW_INPUT_VALUE);
        $inputToMerge->setFilterChain($this->filterChainThatConvertsAllValuesTo(static::A_FILTERED_INPUT_VALUE));

        // Execute
        $this->sut->merge($inputToMerge);
        $result = $this->sut->getRawValue();

        // Assert
        $this->assertSame(static::A_SECOND_RAW_INPUT_VALUE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Depends('getRawValueReturnsTheInputValueWithoutTheFilterChainApplied')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeDoesNotSetValueForInputsThatAreLaminasDoNotHaveAValueSet(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValue(static::A_RAW_INPUT_VALUE);
        $inputToMerge = new Input(static::AN_INPUT_NAME);
        $inputToMerge->setFilterChain($this->filterChainThatConvertsAllValuesTo(static::A_FILTERED_INPUT_VALUE));

        // Execute
        $this->sut->merge($inputToMerge);
        $result = $this->sut->getRawValue();

        // Assert
        $this->assertSame(static::A_RAW_INPUT_VALUE, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('getFilterChainReturnsAFilterChain')]
    #[\PHPUnit\Framework\Attributes\Depends('setFilterChainSetsAFilterChain')]
    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeMergesFilterChain(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setFilterChain($this->filterChainThatAddsSuffix(static::A_STRING_SUFFIX));
        $inputToMerge = new Input(static::AN_INPUT_NAME);
        $inputToMerge->setFilterChain($this->filterChainThatAddsSuffix(static::A_SECOND_STRING_SUFFIX));

        // Execute
        $this->sut->merge($inputToMerge);
        $result = $this->sut->getFilterChain()->filter(static::AN_EMPTY_RAW_INPUT_VALUE);

        // Assert
        $this->assertEquals(static::A_STRING_SUFFIX . static::A_SECOND_STRING_SUFFIX, $result);
    }

    #[\PHPUnit\Framework\Attributes\Depends('setValidatorChainSetsTheValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('getValidatorChainReturnsAValidatorChain')]
    #[\PHPUnit\Framework\Attributes\Depends('mergeIsCallable')]
    #[\PHPUnit\Framework\Attributes\Test]
    public function mergeMergesValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValidatorChain($this->validatorChainWithValidator(new NotEmpty()));
        $inputToMerge = new Input(static::AN_INPUT_NAME);
        $inputToMerge->setValidatorChain($this->validatorChainWithValidator(new NotEmpty()));

        // Execute
        $this->sut->merge($inputToMerge);
        $result = $this->sut->getValidatorChain()->count();

        // Assert
        $this->assertEquals(2, $result);
    }

    protected function setUpSut(mixed $name = null): void
    {
        $this->sut = new ChainValidatedInput($name ?? static::AN_INPUT_NAME);
    }

    protected function emptyValidatorChain(): ValidatorChain
    {
        return new ValidatorChain();
    }

    protected function getNotEmptyValidatorFromValidatorChain(ValidatorChain $validatorChain): ?NotEmpty
    {
        foreach ($validatorChain->getValidators() as $validatorConfig) {
            if (!is_array($validatorConfig)) {
                continue;
            }
            if (!($validatorConfig['instance'] ?? null) instanceof NotEmpty) {
                continue;
            }
            return $validatorConfig['instance'];
        }

        return null;
    }

    protected function validatorChainWithValidator(ValidatorInterface $validator): ValidatorChain
    {
        $chain = new ValidatorChain();
        $chain->attach($validator);
        return $chain;
    }

    protected function errorMessageObjectThatCastsToAString(string $errorMessage): object
    {
        return new readonly class ($errorMessage) {
            public function __construct(private string $val)
            {
            }

            public function __toString(): string
            {
                return $this->val;
            }
        };
    }

    protected function filterChainThatConvertsAllValuesTo(mixed $val): FilterChain
    {
        $filterChain = new FilterChain();
        $filterChain->attach(static fn() => $val);
        return $filterChain;
    }

    protected function filterChainThatAddsSuffix(string $suffix): FilterChain
    {
        $filterChain = new FilterChain();
        $filterChain->attach(static fn($val) => ($val ?? '') . $suffix);
        return $filterChain;
    }

    protected function validatorChainWithErrorMessage(): ValidatorChain
    {
        $chain = new ValidatorChain();
        $chain->attach(new NotEmpty());
        $chain->isValid(null);
        return $chain;
    }
}
