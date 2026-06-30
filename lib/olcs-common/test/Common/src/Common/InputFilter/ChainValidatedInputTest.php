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
class ChainValidatedInputTest extends MockeryTestCase
{
    protected const AN_INPUT_NAME = 'AN INPUT NAME';

    protected const A_CUSTOM_INPUT_NAME = 'A CUSTOM INPUT NAME';

    protected const AN_ALTERNATIVE_INPUT_NAME = 'AN ALTERNATIVE INPUT NAME';

    protected const EXPECTED_STRING_EXCEPTION_MESSAGE = 'Expected string';

    protected const AN_INT = 0;

    protected const REQUIRED = true;

    protected const NOT_REQUIRED = false;

    protected const EXPECTED_BOOL_EXCEPTION_MESSAGE = 'Expected bool';

    protected const ALLOW_EMPTY = true;

    protected const DONT_ALLOW_EMPTY = false;

    protected const BREAK_ON_FAILURE = true;

    protected const DONT_BREAK_ON_FAILURE = false;

    protected const A_CUSTOM_ERROR_MESSAGE = 'AN ERROR MESSAGE';

    protected const A_MESSAGES_ARRAY_CONTAINING_A_CUSTOM_ERROR_MESSAGE = [self::A_CUSTOM_ERROR_MESSAGE];

    protected const NO_ERROR_MESSAGE = null;

    protected const THE_DEFAULT_INPUT_VALUE = null;

    protected const A_RAW_INPUT_VALUE = 'A RAW INPUT VALUE';

    protected const A_SECOND_RAW_INPUT_VALUE = 'A SECOND RAW INPUT VALUE';

    protected const A_FILTERED_INPUT_VALUE = 'A FILTERED INPUT VALUE';

    protected const EMPTY_VALIDATION_CONTEXT = [];

    protected const VALID = true;

    protected const NOT_VALID = false;

    protected const MESSAGES_FOR_A_VALID_INPUT = [];

    protected const MESSAGES_FOR_AN_INVALID_INPUT = ['A VALIDATOR KEY' => 'AN VALIDATION MESSAGE'];

    protected const THE_DEFAULT_ERROR_MESSAGE = null;

    protected const A_STRING_SUFFIX = 'A STRING SUFFIX';

    protected const A_SECOND_STRING_SUFFIX = 'A SECOND STRING SUFFIX';

    protected const AN_EMPTY_RAW_INPUT_VALUE = '';

    /**
     * @var ChainValidatedInput|null
     */
    protected $sut;

    /**
     * @test
     */
    public function getNameIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getName']);
    }

    /**
     * @test
     * @depends getNameIsCallable
     */
    public function getNameReturnsTheNameOfAnInput(): void
    {
        // Setup
        $this->setUpSut(static::AN_INPUT_NAME);

        // Assert
        $this->assertSame(static::AN_INPUT_NAME, $this->sut->getName());
    }

    /**
     * @test
     */
    public function setNameIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setName']);
    }

    /**
     * @test
     * @depends setNameIsCallable
     */
    public function setNameReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertSame($this->sut, $this->sut->setName(static::AN_INPUT_NAME));
    }

    /**
     * @test
     * @depends setNameIsCallable
     * @depends getNameReturnsTheNameOfAnInput
     */
    public function setNameSetsTheName(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setName(static::AN_ALTERNATIVE_INPUT_NAME);

        // Assert
        $this->assertSame(static::AN_ALTERNATIVE_INPUT_NAME, $this->sut->getName());
    }

    /**
     * @test
     * @depends setNameIsCallable
     * @depends getNameReturnsTheNameOfAnInput
     */
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

    /**
     * @test
     */
    public function isRequiredIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'isRequired']);
    }

    /**
     * @test
     * @depends isRequiredIsCallable
     */
    public function isRequiredReturnsABoolean(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->isRequired();

        // Assert
        $this->assertIsBool($result);
    }

    /**
     * @test
     * @depends isRequiredReturnsABoolean
     */
    public function isRequiredReturnsTrueByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->isRequired();

        // Assert
        $this->assertSame(true, $result);
    }


    /**
     * @test
     */
    public function setRequiredIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setRequired']);
    }

    /**
     * @test
     * @depends setRequiredIsCallable
     */
    public function setRequiredReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->setRequired(static::REQUIRED);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @test
     * @depends setRequiredIsCallable
     */
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

    /**
     * @test
     * @depends setRequiredIsCallable
     * @depends isRequiredReturnsABoolean
     */
    public function setRequiredSetsAnInputAsRequired(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setRequired(static::REQUIRED);

        // Assert
        $this->assertSame(static::REQUIRED, $this->sut->isRequired());
    }

    /**
     * @test
     * @depends setRequiredIsCallable
     * @depends isRequiredReturnsABoolean
     */
    public function setRequiredSetsAnInputAsNotRequired(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setRequired(static::NOT_REQUIRED);

        // Assert
        $this->assertSame(static::NOT_REQUIRED, $this->sut->isRequired());
    }

    /**
     * @test
     */
    public function getValidatorChainIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getValidatorChain']);
    }


    /**
     * @test
     * @depends getValidatorChainIsCallable
     */
    public function getValidatorChainReturnsAValidatorChain(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertInstanceOf(ValidatorChain::class, $this->sut->getValidatorChain());
    }

    /**
     * @test
     * @depends getValidatorChainReturnsAValidatorChain
     */
    public function getValidatorChainThatIsEmptyByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getValidatorChain();

        // Assert
        $this->assertEmpty($result->getValidators());
    }

    /**
     * @test
     */
    public function setValidatorChainIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setValidatorChain']);
    }

    /**
     * @test
     * @depends setValidatorChainIsCallable
     */
    public function setValidatorChainReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->setValidatorChain($this->emptyValidatorChain());

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @test
     * @depends setValidatorChainIsCallable
     * @depends getValidatorChainIsCallable
     */
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

    /**
     * @test
     */
    public function allowEmptyIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'allowEmpty']);
    }

    /**
     * @test
     * @depends allowEmptyIsCallable
     * @depends getValidatorChainReturnsAValidatorChain
     */
    public function allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $validatorChain = $this->sut->getValidatorChain();
        $validatorChain->attach(new NotEmpty());

        // Assert
        $this->assertSame(static::ALLOW_EMPTY, $this->sut->allowEmpty());
    }

    /**
     * @test
     * @depends allowEmptyIsCallable
     * @depends setValidatorChainSetsTheValidatorChain
     */
    public function allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValidatorChain($this->emptyValidatorChain());

        // Assert
        $this->assertSame(static::DONT_ALLOW_EMPTY, $this->sut->allowEmpty());
    }

    /**
     * @test
     */
    public function setAllowEmptyIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setAllowEmpty']);
    }

    /**
     * @test
     * @depends setAllowEmptyIsCallable
     */
    public function setAllowEmptyReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->setAllowEmpty(static::ALLOW_EMPTY);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @test
     * @depends setAllowEmptyIsCallable
     */
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

    /**
     * @test
     * @depends setAllowEmptyIsCallable
     * @depends allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain
     * @depends allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain
     */
    public function setAllowEmptyWhenPassedFalseAndNotEmptyValidatorIsNotPresentInTheValidatorChainAddsNotEmptyValidatorToTheValidatorChain(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setAllowEmpty(static::DONT_ALLOW_EMPTY);

        // Assert
        $this->assertNotNull($this->getNotEmptyValidatorFromValidatorChain($this->sut->getValidatorChain()));
    }

    /**
     * @test
     * @depends setAllowEmptyIsCallable
     * @depends allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain
     * @depends allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain
     * @depends setValidatorChainSetsTheValidatorChain
     */
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

    /**
     * @test
     * @depends setAllowEmptyIsCallable
     * @depends allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain
     * @depends allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain
     * @depends setValidatorChainSetsTheValidatorChain
     */
    public function setAllowEmptyWhenPassedTrueAndNotEmptyValidatorIsPresentInTheValidatorChainRemovesNotEmptyValidatorFromTheValidatorChain(): void
    {
        // Setup
        $this->setUpSut();
        $notEmptyValidator = new NotEmpty();
        $this->sut->setValidatorChain($this->validatorChainWithValidator($notEmptyValidator));

        // Execute
        $this->sut->setAllowEmpty(static::ALLOW_EMPTY);

        // Assert
        $this->assertNull($this->getNotEmptyValidatorFromValidatorChain($this->sut->getValidatorChain()));
    }

    /**
     * @test
     * @depends setAllowEmptyIsCallable
     * @depends allowEmptyReturnsTrueIfNotEmptyValidatorExistsInTheValidatorChain
     * @depends allowEmptyReturnsFalseIfNotEmptyValidatorDoesNotExistInTheValidatorChain
     * @depends setValidatorChainSetsTheValidatorChain
     */
    public function setAllowEmptyWhenPassedTrueAndNotEmptyValidatorIsNotPresentInTheValidatorChainDoesNotAddNotEmptyValidator(): void
    {
        // Setup
        $this->setUpSut();
        $this->sut->setValidatorChain($this->emptyValidatorChain());

        // Execute
        $this->sut->setAllowEmpty(static::ALLOW_EMPTY);

        // Assert
        $this->assertNull($this->getNotEmptyValidatorFromValidatorChain($this->sut->getValidatorChain()));
    }

    /**
     * @test
     */
    public function breakOnFailureIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'breakOnFailure']);
    }

    /**
     * @test
     * @depends breakOnFailureIsCallable
     */
    public function breakOnFailureReturnsFalseByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertEquals(static::DONT_BREAK_ON_FAILURE, $this->sut->breakOnFailure());
    }

    /**
     * @test
     */
    public function setBreakOnFailureIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setBreakOnFailure']);
    }

    /**
     * @test
     * @depends setBreakOnFailureIsCallable
     */
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

    /**
     * @test
     * @depends setBreakOnFailureIsCallable
     * @depends breakOnFailureIsCallable
     */
    public function setBreakOnFailureSetsTheBreakOnFailureFlagToTrue(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setBreakOnFailure(static::BREAK_ON_FAILURE);

        // Assert
        $this->assertSame(static::BREAK_ON_FAILURE, $this->sut->breakOnFailure());
    }

    /**
     * @test
     */
    public function getErrorMessageIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getErrorMessage']);
    }

    /**
     * @test
     * @depends getErrorMessageIsCallable
     */
    public function getErrorMessageReturnsNullByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getErrorMessage();

        // Assert
        $this->assertSame(static::THE_DEFAULT_ERROR_MESSAGE, $result);
    }

    /**
     * @test
     */
    public function setErrorMessageIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setErrorMessage']);
    }

    /**
     * @test
     * @depends getErrorMessageIsCallable
     * @depends setErrorMessageIsCallable
     */
    public function setErrorMessageWhenProvidedAStringSetsTheString(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setErrorMessage(static::A_CUSTOM_ERROR_MESSAGE);

        // Assert
        $this->assertSame(static::A_CUSTOM_ERROR_MESSAGE, $this->sut->getErrorMessage());
    }

    /**
     * @test
     * @depends getErrorMessageIsCallable
     * @depends setErrorMessageIsCallable
     */
    public function setErrorMessageWhenProvidedNullSetsNull(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setErrorMessage(static::NO_ERROR_MESSAGE);

        // Assert
        $this->assertSame(static::NO_ERROR_MESSAGE, $this->sut->getErrorMessage());
    }

    /**
     * @test
     * @depends getErrorMessageIsCallable
     * @depends setErrorMessageIsCallable
     */
    public function setErrorMessageWhenProvidedAnObjectSetsStringRepresentationOfAnObject(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $this->sut->setErrorMessage($this->errorMessageObjectThatCastsToAString(static::A_CUSTOM_ERROR_MESSAGE));

        // Assert
        $this->assertSame(static::A_CUSTOM_ERROR_MESSAGE, $this->sut->getErrorMessage());
    }

    /**
     * @test
     */
    public function getFilterChainIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getFilterChain']);
    }

    /**
     * @test
     */
    public function getFilterChainReturnsAFilterChain(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getFilterChain();

        // Assert
        $this->assertInstanceOf(FilterChain::class, $result);
    }

    /**
     * @test
     */
    public function setFilterChainIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setFilterChain']);
    }

    /**
     * @test
     * @depends setFilterChainIsCallable
     */
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

    /**
     * @test
     * @depends setFilterChainIsCallable
     * @depends getFilterChainReturnsAFilterChain
     */
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

    /**
     * @test
     */
    public function getRawValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getRawValue']);
    }

    /**
     * @test
     * @depends getRawValueIsCallable
     */
    public function getRawValueReturnsNullByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getRawValue();

        // Assert
        $this->assertSame(static::THE_DEFAULT_INPUT_VALUE, $result);
    }

    /**
     * @test
     * @depends getRawValueIsCallable
     * @depends setFilterChainSetsAFilterChain
     */
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

    /**
     * @test
     */
    public function getValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getValue']);
    }

    /**
     * @test
     */
    public function getValueReturnsNullByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getValue();

        // Assert
        $this->assertSame(static::THE_DEFAULT_INPUT_VALUE, $result);
    }

    /**
     * @test
     * @depends getValueIsCallable
     * @depends setFilterChainSetsAFilterChain
     */
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

    /**
     * @test
     */
    public function setValueIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'setValue']);
    }

    /**
     * @test
     * @depends setValueIsCallable
     */
    public function setValueReturnsSelf(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->setValue(static::A_RAW_INPUT_VALUE);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @test
     * @depends setValueIsCallable
     * @depends getRawValueReturnsTheInputValueWithoutTheFilterChainApplied
     */
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

    /**
     * @test
     */
    public function isValidIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'isValid']);
    }

    /**
     * @test
     */
    public function isValidReturnsBoolean(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsBool($this->sut->isValid());
    }

    /**
     * @test
     */
    public function isValidReturnsTrueByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertSame(static::VALID, $this->sut->isValid());
    }

    /**
     * @test
     * @depends isValidReturnsBoolean
     * @depends setValidatorChainSetsTheValidatorChain
     * @depends setValueSetsAValue
     * @depends setFilterChainSetsAFilterChain
     */
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

    /**
     * @test
     */
    public function getMessagesIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'getMessages']);
    }

    /**
     * @test
     * @depends getMessagesIsCallable
     */
    public function getMessagesReturnsAnArray(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getMessages();

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * @test
     * @depends getMessagesReturnsAnArray
     */
    public function getMessagesReturnsAnEmptyArrayByDefault(): void
    {
        // Setup
        $this->setUpSut();

        // Execute
        $result = $this->sut->getMessages();

        // Assert
        $this->assertSame(static::MESSAGES_FOR_A_VALID_INPUT, $result);
    }

    /**
     * @test
     * @depends getMessagesReturnsAnArray
     * @depends setValidatorChainSetsTheValidatorChain
     */
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

    /**
     * @test
     * @depends getMessagesReturnsAnArray
     * @depends setErrorMessageWhenProvidedAStringSetsTheString
     */
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

    /**
     * @test
     * @depends getMessagesReturnsAnArrayWithCustomErrorMessage
     */
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

    /**
     * @test
     */
    public function mergeIsCallable(): void
    {
        // Setup
        $this->setUpSut();

        // Assert
        $this->assertIsCallable([$this->sut, 'merge']);
    }

    /**
     * @test
     * @depends mergeIsCallable
     */
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

    /**
     * @test
     * @depends mergeIsCallable
     * @depends setBreakOnFailureSetsTheBreakOnFailureFlagToTrue
     */
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

    /**
     * @test
     * @depends mergeIsCallable
     * @depends setErrorMessageWhenProvidedAStringSetsTheString
     * @depends getErrorMessageIsCallable
     */
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

    /**
     * @test
     * @depends mergeIsCallable
     * @depends setNameSetsTheName
     * @depends getNameReturnsTheNameOfAnInput
     */
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

    /**
     * @test
     * @depends mergeIsCallable
     * @depends isRequiredReturnsABoolean
     * @depends setRequiredSetsAnInputAsNotRequired
     */
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

    /**
     * @test
     * @depends mergeIsCallable
     * @depends setValueSetsAValue
     * @depends setFilterChainSetsAFilterChain
     * @depends getRawValueReturnsTheInputValueWithoutTheFilterChainApplied
     */
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

    /**
     * @test
     * @depends mergeIsCallable
     * @depends getRawValueReturnsTheInputValueWithoutTheFilterChainApplied
     */
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

    /**
     * @test
     * @depends mergeIsCallable
     * @depends getRawValueReturnsTheInputValueWithoutTheFilterChainApplied
     */
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

    /**
     * @test
     * @depends getFilterChainReturnsAFilterChain
     * @depends setFilterChainSetsAFilterChain
     * @depends mergeIsCallable
     */
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

    /**
     * @test
     * @depends setValidatorChainSetsTheValidatorChain
     * @depends getValidatorChainReturnsAValidatorChain
     * @depends mergeIsCallable
     */
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
        return new class ($errorMessage) {
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
