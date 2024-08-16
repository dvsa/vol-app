<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Form;

use Common\Test\MockeryTestCase;
use Laminas\Form\Element\Csrf;
use Common\Form\Element\Button;
use Laminas\Validator\InArray;
use Common\Form\Elements\Custom\RadioVertical;
use Laminas\Form\Element\Hidden;
use Common\InputFilter\ChainValidatedInput;

/**
 * @see AddVehiclesQuestionForm
 */
class AddVehiclesQuestionFormTest extends MockeryTestCase
{
    protected const A_FORM_NAME = 'A FORM NAME';
    protected const A_RADIO_ELEMENT_NAME = 'A RADIO ELEMENT NAME';
    protected const SECURITY = 'security';
    protected const EMPTY_ARRAY_VALUE = [];
    protected const INVALID_CSRF_VALUE = 'AN INVALID CSRF VALUE';
    protected const CSRF_KEY = 'security';
    protected const CSRF_NAME = 'security';
    protected const NEXT_BUTTON_KEY = 'btn_next';
    protected const NEXT_BUTTON_NAME = 'submit';
    protected const NEXT_BUTTON_VALUE = 'next';
    protected const NEXT_BUTTON_LABEL = 'Next';
    protected const RETURN_TO_OVERVIEW_BUTTON_KEY = 'btn_overview';
    protected const RETURN_TO_OVERVIEW_BUTTON_NAME = 'submit';
    protected const RETURN_TO_OVERVIEW_BUTTON_LABEL = 'application.vehicle.add-details.action.save-and-return';
    protected const RETURN_TO_OVERVIEW_BUTTON_VALUE = 'overview';
    protected const SUBMIT_KEY = 'submit';
    protected const AN_INVALID_SUBMIT_VALUE = 'AN INVALID SUBMIT VALUE';
    protected const INVALID_SUBMIT_VALIDATION_MESSAGE = 'An error occurred, please try again';
    protected const RADIO_KEY = 'radio';
    protected const RADIO_NAME = 'radio';
    protected const RADIO_LABEL = 'application.vehicle.add-details.radio.label';
    protected const RADIO_HINT = 'application.vehicle.add-details.radio.hint';
    protected const YES_VALUE_OPTION_KEY = 'yes';
    protected const YES_VALUE_OPTION_LABEL = 'Yes';
    protected const YES_VALUE_OPTION_VALUE = 1;
    protected const NO_VALUE_OPTION_KEY = 'no';
    protected const NO_VALUE_OPTION_LABEL = 'No';
    protected const NO_VALUE_OPTION_VALUE = 0;
    protected const NO_VALUE_OPTION_CONDITIONAL_CONTENT = 'application.vehicle.add-details.radio.option.no.conditional-content';
    protected const VALUE_OPTION_SELECTED_KEY = 'selected';
    protected const VALUE_OPTION_LABEL_KEY = 'label';
    protected const VALUE_OPTION_VALUE_KEY = 'value';
    protected const VALUE_OPTION_CONDITIONAL_CONTENT_KEY = 'conditional_content';
    protected const EMPTY_STRING_VALUE = '';
    protected const NULL_VALUE = null;
    protected const INVALID_RADIO_OPTION_VALUE = 'INVALID RADIO OPTION';
    protected const INVALID_RADIO_VALIDATION_MESSAGE = 'application.vehicle.add-details.radio.messages.not-in-array';
    protected const AN_APPLICATION_VERSION_ELEMENT_NAME = 'application-version';
    protected const APPLICATION_VERSION_KEY = 'application-version';
    protected const POSITIVE_INTEGER = 1;
    protected const POSITIVE_INTEGER_STRING = '1';
    protected const AN_APPLICATION_VERSION = 1;
    protected const IS_REQUIRED = true;
    protected const IS_NOT_REQUIRED = false;
    protected const IS_VALID = true;

    /**
     * @var AddVehiclesQuestionForm|null
     */
    protected $sut;

    /**
     * @test
     */
    public function constructInitialisesACsrfElement()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->get(static::CSRF_KEY);

        // Assert
        $this->assertInstanceOf(Csrf::class, $result);
    }

    /**
     * @test
     */
    public function constructInitialisesANextButtonElement()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->get(static::NEXT_BUTTON_KEY);

        // Assert
        $this->assertInstanceOf(Button::class, $result);
    }

    /**
     * @test
     */
    public function constructInitialisesAReturnToOverviewButtonElement()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->get(static::RETURN_TO_OVERVIEW_BUTTON_KEY);

        // Assert
        $this->assertInstanceOf(Button::class, $result);
    }

    /**
     * @test
     */
    public function constructInitialisesARadioElement()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->get(static::RADIO_KEY);

        // Assert
        $this->assertInstanceOf(RadioVertical::class, $result);
    }

    /**
     * @test
     */
    public function constructInitialisesAnApplicationVersionElement()
    {
        // Setup
        $this->setUpSut(static::AN_APPLICATION_VERSION_ELEMENT_NAME);

        // Execute
        $result = $this->sut->get(static::APPLICATION_VERSION_KEY);

        // Assert
        $this->assertInstanceOf(Hidden::class, $result);
    }

    /**
     * @test
     */
    public function getNextButtonElementIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getNextButtonElement']);
    }

    /**
     * @test
     *
     * @depends getNextButtonElementIsCallable
     * @depends _constructInitialisesANextButtonElement
     */
    public function getNextButtonElementReturnsAnInstanceOfButton(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertInstanceOf(Button::class, $this->sut->getNextButtonElement());
    }

    /**
     * @test
     *
     * @depends getNextButtonElementReturnsAnInstanceOfButton
     */
    public function getNextButtonElementReturnsAnInstanceOfButtonWithAName(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertEquals(static::NEXT_BUTTON_NAME, $this->sut->getNextButtonElement()->getName());
    }

    /**
     * @test
     *
     * @depends getNextButtonElementReturnsAnInstanceOfButton
     */
    public function getNextButtonElementReturnsAnInstanceOfButtonWithAValue(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertEquals(static::NEXT_BUTTON_VALUE, $this->sut->getNextButtonElement()->getValue());
    }

    /**
     * @test
     *
     * @depends getNextButtonElementReturnsAnInstanceOfButton
     */
    public function getNextButtonElementReturnsAnInstanceOfButtonWithALabel(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertEquals(static::NEXT_BUTTON_LABEL, $this->sut->getNextButtonElement()->getLabel());
    }

    /**
     * @test
     */
    public function getReturnToOverviewButtonElementIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getReturnToOverviewButtonElement']);
    }

    /**
     * @test
     *
     * @depends _constructInitialisesAReturnToOverviewButtonElement
     */
    public function getReturnToOverviewButtonElementReturnsAnInstanceOfButton(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getReturnToOverviewButtonElement();

        // Assert
        $this->assertInstanceOf(Button::class, $result);
    }

    /**
     * @test
     *
     * @depends getReturnToOverviewButtonElementReturnsAnInstanceOfButton
     */
    public function getReturnToOverviewButtonElementReturnsAnInstanceOfButtonWithAName(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getReturnToOverviewButtonElement()->getName();

        // Assert
        $this->assertEquals(static::RETURN_TO_OVERVIEW_BUTTON_NAME, $result);
    }

    /**
     * @test
     *
     * @depends getReturnToOverviewButtonElementReturnsAnInstanceOfButton
     */
    public function getReturnToOverviewButtonElementReturnsAnInstanceOfButtonWithAValue(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getReturnToOverviewButtonElement()->getValue();

        // Assert
        $this->assertEquals(static::RETURN_TO_OVERVIEW_BUTTON_VALUE, $result);
    }

    /**
     * @test
     *
     * @depends getReturnToOverviewButtonElementReturnsAnInstanceOfButton
     */
    public function getReturnToOverviewButtonElementReturnsAnInstanceOfButtonWithALabel(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getReturnToOverviewButtonElement()->getLabel();

        // Assert
        $this->assertEquals(static::RETURN_TO_OVERVIEW_BUTTON_LABEL, $result);
    }

    /**
     * @test
     */
    public function getRadioElementIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getRadioElement']);
    }

    /**
     * @test
     *
     * @depends getRadioElementIsCallable
     * @depends _constructInitialisesARadioElement
     */
    public function getRadioElementReturnsARadio(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement();

        // Assert
        $this->assertInstanceOf(RadioVertical::class, $result);
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadio
     */
    public function getRadioElementReturnsARadioWithName(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement();

        // Assert
        $this->assertEquals(static::RADIO_NAME, $result->getName());
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadio
     */
    public function getRadioElementReturnsARadioWithLabel(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement();

        // Assert
        $this->assertEquals(static::RADIO_LABEL, $result->getLabel());
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadio
     */
    public function getRadioElementReturnsARadioWithHintOption(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement();

        // Assert
        $this->assertEquals(static::RADIO_HINT, $result->getOption('hint'));
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadio
     */
    public function getRadioElementReturnsARadioWithYesOption(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement()->getValueOptions()[static::YES_VALUE_OPTION_KEY] ?? null;

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadioWithYesOption
     */
    public function getRadioElementReturnsARadioWithYesOptionThatIsNotSelected(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement()->getValueOptions()[static::YES_VALUE_OPTION_KEY];

        // Assert
        $this->assertFalse($result[static::VALUE_OPTION_SELECTED_KEY] ?? false);
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadioWithYesOption
     */
    public function getRadioElementReturnsARadioWithYesOptionWithLabel(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement()->getValueOptions()[static::YES_VALUE_OPTION_KEY];

        // Assert
        $this->assertEquals(static::YES_VALUE_OPTION_LABEL, $result[static::VALUE_OPTION_LABEL_KEY]);
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadio
     */
    public function getRadioElementReturnsARadioWithYesOptionWithValue(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement()->getValueOptions()[static::YES_VALUE_OPTION_KEY];

        // Assert
        $this->assertEquals(static::YES_VALUE_OPTION_VALUE, $result[static::VALUE_OPTION_VALUE_KEY]);
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadio
     */
    public function getRadioElementReturnsARadioWithNoOption(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement()->getValueOptions()[static::NO_VALUE_OPTION_KEY] ?? null;

        // Assert
        $this->assertIsArray($result);
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadioWithNoOption
     */
    public function getRadioElementReturnsARadioWithNoOptionThatIsNotSelected(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement()->getValueOptions()[static::NO_VALUE_OPTION_KEY];

        // Assert
        $this->assertFalse($result[static::VALUE_OPTION_SELECTED_KEY] ?? false);
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadioWithNoOption
     */
    public function getRadioElementReturnsARadioWithNoOptionWithLabel(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement()->getValueOptions()[static::NO_VALUE_OPTION_KEY];

        // Assert
        $this->assertEquals(static::NO_VALUE_OPTION_LABEL, $result[static::VALUE_OPTION_LABEL_KEY]);
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadioWithNoOption
     */
    public function getRadioElementReturnsARadioWithNoOptionWithValue(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement()->getValueOptions()[static::NO_VALUE_OPTION_KEY];

        // Assert
        $this->assertEquals(static::NO_VALUE_OPTION_VALUE, $result[static::VALUE_OPTION_VALUE_KEY]);
    }

    /**
     * @test
     *
     * @depends getRadioElementReturnsARadioWithNoOption
     */
    public function getRadioElementReturnsARadioWithNoOptionWithConditionalContent(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioElement()->getValueOptions()[static::NO_VALUE_OPTION_KEY];

        // Assert
        $this->assertEquals(static::NO_VALUE_OPTION_CONDITIONAL_CONTENT, $result[static::VALUE_OPTION_CONDITIONAL_CONTENT_KEY]);
    }

    /**
     * @test
     */
    public function getSubmitInputIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getSubmitInput']);
    }

    /**
     * @test
     *
     * @depends getSubmitInputIsCallable
     */
    public function getSubmitInputReturnsAnInput(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertInstanceOf(ChainValidatedInput::class, $this->sut->getSubmitInput());
    }

    /**
     * @test
     *
     * @depends getSubmitInputReturnsAnInput
     */
    public function getSubmitInputReturnsInstanceOfInputInterfaceThatIsRequired(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $this->sut->setData(static::EMPTY_ARRAY_VALUE);
        $result = $this->sut->getSubmitInput()->isRequired();

        // Assert
        $this->assertSame(static::IS_REQUIRED, $result);
    }

    /**
     * @test
     *
     * @depends getSubmitInputReturnsAnInput
     */
    public function getSubmitInputReturnsInstanceOfInputInterfaceThatRejectsAnInvalidValue(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $this->sut->setData([static::SUBMIT_KEY => static::AN_INVALID_SUBMIT_VALUE]);
        $this->sut->isValid();

        // Assert
        $this->assertNotNull($this->sut->getMessages()[static::SUBMIT_KEY][InArray::NOT_IN_ARRAY] ?? null);
    }

    /**
     * @test
     *
     * @depends getSubmitInputReturnsInstanceOfInputInterfaceThatRejectsAnInvalidValue
     */
    public function getSubmitInputReturnsInstanceOfInputInterfaceThatRejectsAnInvalidValueWithACustomMessage(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $this->sut->setData([static::SUBMIT_KEY => static::AN_INVALID_SUBMIT_VALUE]);
        $this->sut->isValid();

        // Assert
        $this->assertEquals(static::INVALID_SUBMIT_VALIDATION_MESSAGE, $this->sut->getMessages()[static::SUBMIT_KEY][InArray::NOT_IN_ARRAY]);
    }

    /**
     * @test
     */
    public function getRadioInputIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getRadioInput']);
    }

    /**
     * @test
     *
     * @depends getRadioInputIsCallable
     * @depends _constructInitialisesARadioElement
     */
    public function getRadioInputReturnsAnInput(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioInput();

        // Assert
        $this->assertInstanceOf(ChainValidatedInput::class, $result);
    }

    /**
     * @return array
     */
    public function validSubmitValuesDataProvider(): array
    {
        return [
            'return to overview button value' => [static::RETURN_TO_OVERVIEW_BUTTON_VALUE],
            'next button value' => [static::NEXT_BUTTON_VALUE],
        ];
    }

    /**
     * @test
     *
     * @depends getRadioInputReturnsAnInput
     *
     * @dataProvider validSubmitValuesDataProvider
     */
    public function getRadioInputReturnsInputFilterThatAcceptsValidValues(string $value): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $this->sut->setData([static::SUBMIT_KEY => $value]);
        $this->sut->isValid();

        // Assert
        $this->assertNull($this->sut->getMessages()[static::SUBMIT_KEY] ?? null);
    }

    /**
     * @test
     *
     * @depends getRadioInputReturnsAnInput
     */
    public function getRadioInputReturnsInputFilterThatIsRequired(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $this->sut->setData(static::EMPTY_ARRAY_VALUE);
        $result = $this->sut->getRadioInput()->isRequired();

        // Assert
        $this->assertSame(static::IS_REQUIRED, $result);
    }

    /**
     * @return array
     */
    public function invalidRadioValueDataProvider(): array
    {
        return [
            'empty string value' => [static::EMPTY_STRING_VALUE],
            'non-empty string value' => [static::INVALID_RADIO_OPTION_VALUE],
            'float' => ['1.0'],
            'null' => [null],
            'empty array' => [[]],
        ];
    }

    /**
     *
     * @test
     *
     * @depends getRadioInputReturnsAnInput
     * @dataProvider invalidRadioValueDataProvider
     */
    public function getRadioInputReturnsInputFilterThatRejectsAnInvalidValue(mixed $value): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $this->sut->setData([static::RADIO_KEY => $value]);
        $this->sut->isValid();

        // Assert
        $this->assertNotNull($this->sut->getMessages()[static::RADIO_KEY] ?? null);
    }

    /**
     * @test
     *
     * @depends getRadioInputReturnsAnInput
     */
    public function getRadioInputReturnsInputFilterThatRejectsAnInvalidValueWithCustomMessageForInArrayNotInArrayRule(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->getRadioInput()->setValue(static::INVALID_RADIO_OPTION_VALUE);
        $this->sut->getRadioInput()->isValid();

        // Execute
        $result = $this->sut->getRadioInput()->getMessages()[InArray::NOT_IN_ARRAY];

        // Assert
        $this->assertEquals(static::INVALID_RADIO_VALIDATION_MESSAGE, $result);
    }

    /**
     * @return array
     */
    public function yesRadioValidValuesDataProvider(): array
    {
        return [
            'yes value - int' => [static::YES_VALUE_OPTION_VALUE],
            'yes value - string' => [(string) static::YES_VALUE_OPTION_VALUE],
        ];
    }

    /**
     * @return array
     */
    public function noRadioValidValuesDataProvider(): array
    {
        return [
            'no value - int' => [static::NO_VALUE_OPTION_VALUE],
            'no value - string' => [(string) static::NO_VALUE_OPTION_VALUE],
        ];
    }

    /**
     * @return array
     */
    public function radioValidValuesDataProvider(): array
    {
        return array_merge($this->yesRadioValidValuesDataProvider(), $this->noRadioValidValuesDataProvider());
    }

    /**
     * @test
     */
    public function isValidIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'isValid']);
    }

    /**
     * @test
     */
    public function userHasOptedToContinueToTheNextStepIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'userHasOptedToContinueToTheNextStep']);
    }

    /**
     * @test
     *
     * @depends userHasOptedToContinueToTheNextStepIsCallable
     * @depends _constructInitialisesANextButtonElement
     * @depends isValidIsCallable
     */
    public function userHasOptedToContinueToTheNextStepReturnsTrueWhenSubmitValueIsNext(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->setData([static::SUBMIT_KEY => static::NEXT_BUTTON_VALUE]);
        $this->sut->isValid();

        // Execute
        $result = $this->sut->userHasOptedToContinueToTheNextStep();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     *
     * @depends userHasOptedToContinueToTheNextStepIsCallable
     * @depends _constructInitialisesANextButtonElement
     * @depends isValidIsCallable
     */
    public function userHasOptedToContinueToTheNextStepReturnsFalseWhenSubmitValueIsOverview(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->setData([static::SUBMIT_KEY => static::RETURN_TO_OVERVIEW_BUTTON_VALUE]);
        $this->sut->isValid();

        // Execute
        $result = $this->sut->userHasOptedToContinueToTheNextStep();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @test
     *
     * @depends userHasOptedToContinueToTheNextStepIsCallable
     * @depends _constructInitialisesANextButtonElement
     * @depends isValidIsCallable
     */
    public function userHasOptedToContinueToTheNextStepReturnsFalseWhenSubmitValueIsInvalid(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->setData([static::SUBMIT_KEY => static::AN_INVALID_SUBMIT_VALUE]);
        $this->sut->isValid();

        // Execute
        $result = $this->sut->userHasOptedToContinueToTheNextStep();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function userHasOptedToSubmitVehicleDetailsIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'userHasOptedToSubmitVehicleDetails']);
    }

    /**
     *
     * @test
     *
     * @depends userHasOptedToSubmitVehicleDetailsIsCallable
     * @depends _constructInitialisesARadioElement
     * @depends isValidIsCallable
     * @dataProvider yesRadioValidValuesDataProvider
     */
    public function userHasOptedToSubmitVehicleDetailsReturnsTrueWhenRadioValueIsYes(mixed $yesValue): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->setData([static::RADIO_KEY => $yesValue]);
        $this->sut->isValid();

        // Execute
        $result = $this->sut->userHasOptedToSubmitVehicleDetails();

        // Assert
        $this->assertTrue($result);
    }

    /**
     *
     * @test
     *
     * @depends userHasOptedToSubmitVehicleDetailsIsCallable
     * @depends _constructInitialisesARadioElement
     * @depends isValidIsCallable
     * @dataProvider noRadioValidValuesDataProvider
     */
    public function userHasOptedToSubmitVehicleDetailsReturnsFalseWhenRadioValueIsNo(mixed $noValue): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->setData([static::RADIO_KEY => $noValue]);
        $this->sut->isValid();

        // Execute
        $result = $this->sut->userHasOptedToSubmitVehicleDetails();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @test
     *
     * @depends userHasOptedToSubmitVehicleDetailsIsCallable
     * @depends _constructInitialisesARadioElement
     * @depends isValidIsCallable
     */
    public function userHasOptedToSubmitVehicleDetailsReturnsFalseWhenRadioValueIsInvalid(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->setData([static::RADIO_KEY => static::INVALID_RADIO_OPTION_VALUE]);
        $this->sut->isValid();

        // Execute
        $result = $this->sut->userHasOptedToSubmitVehicleDetails();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function userHasOptedNotToSubmitVehicleDetailsIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'userHasOptedNotToSubmitVehicleDetails']);
    }

    /**
     *
     * @test
     *
     * @depends userHasOptedNotToSubmitVehicleDetailsIsCallable
     * @depends _constructInitialisesARadioElement
     * @depends isValidIsCallable
     * @dataProvider noRadioValidValuesDataProvider
     */
    public function userHasOptedNotToSubmitVehicleDetailsReturnsTrueWhenRadioValueIsNo(mixed $noValue): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->setData([static::RADIO_KEY => $noValue]);
        $this->sut->isValid();

        // Execute
        $result = $this->sut->userHasOptedNotToSubmitVehicleDetails();

        // Assert
        $this->assertTrue($result);
    }

    /**
     *
     * @test
     *
     * @depends userHasOptedNotToSubmitVehicleDetailsIsCallable
     * @depends _constructInitialisesARadioElement
     * @depends isValidIsCallable
     * @dataProvider yesRadioValidValuesDataProvider
     */
    public function userHasOptedNotToSubmitVehicleDetailsReturnsFalseWhenRadioValueIsYes(mixed $yesValue): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->setData([static::RADIO_KEY => $yesValue]);
        $this->sut->isValid();

        // Execute
        $result = $this->sut->userHasOptedNotToSubmitVehicleDetails();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @test
     *
     * @depends userHasOptedNotToSubmitVehicleDetailsIsCallable
     * @depends _constructInitialisesARadioElement
     * @depends isValidIsCallable
     */
    public function userHasOptedNotToSubmitVehicleDetailsReturnsFalseWhenRadioValueIsInvalid(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);
        $this->sut->setData([static::RADIO_KEY => static::INVALID_RADIO_OPTION_VALUE]);
        $this->sut->isValid();

        // Execute
        $result = $this->sut->userHasOptedNotToSubmitVehicleDetails();

        // Assert
        $this->assertFalse($result);
    }

    /**
     * @test
     */
    public function getApplicationVersionElementIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getApplicationVersionElement']);
    }

    /**
     * @test
     *
     * @depends getApplicationVersionElementIsCallable
     * @depends _constructInitialisesAnApplicationVersionElement
     */
    public function getApplicationVersionElementReturnsInstanceOfHidden(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getApplicationVersionElement();

        // Assert
        $this->assertInstanceOf(Hidden::class, $result);
    }

    /**
     * @test
     */
    public function getApplicationVersionInputIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getApplicationVersionInput']);
    }

    /**
     * @test
     *
     * @depends getApplicationVersionInputIsCallable
     */
    public function getApplicationVersionInputReturnsAnInput(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getApplicationVersionInput();

        // Assert
        $this->assertInstanceOf(ChainValidatedInput::class, $result);
    }

    /**
     * @test
     *
     * @depends getApplicationVersionInputIsCallable
     */
    public function getApplicationVersionInputReturnsAnInstanceOfInputWithAnEmptyValidatorChain(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getApplicationVersionInput()->getValidatorChain();

        // Assert
        $this->assertSame(0, $result->count());
    }

    /**
     * @test
     *
     * @depends getApplicationVersionInputReturnsAnInput
     */
    public function getApplicationVersionInputIsNotRequired(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getApplicationVersionInput()->isRequired();

        // Assert
        $this->assertSame(static::IS_NOT_REQUIRED, $result);
    }

    /**
     * @test
     */
    public function selectNoIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'selectNo']);
    }

    /**
     * @test
     *
     * @depends selectNoIsCallable
     */
    public function selectNoReturnsSelf(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->selectNo();

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @test
     *
     * @depends selectNoIsCallable
     */
    public function selectNoAddsRadioToData(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->selectNo();
        $result->isValid();

        // Assert
        $this->assertSame(static::NO_VALUE_OPTION_VALUE, $this->sut->getData()[static::RADIO_KEY] ?? null);
    }

    /**
     * @test
     *
     * @depends selectNoIsCallable
     * @depends getRadioInputReturnsAnInput
     */
    public function selectNoAddsRadioToInputFilterData(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->selectNo();
        $result->isValid();

        // Assert
        $this->assertSame(static::NO_VALUE_OPTION_VALUE, $this->sut->getRadioInput()->getValue('application-version'));
    }

    /**
     * @test
     */
    public function selectYesIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'selectYes']);
    }

    /**
     * @test
     *
     * @depends selectYesIsCallable
     */
    public function selectYesReturnsSelf(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->selectYes();

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @test
     *
     * @depends selectYesIsCallable
     */
    public function selectYesAddsRadioToData(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->selectYes();
        $result->isValid();

        // Assert
        $this->assertSame(static::YES_VALUE_OPTION_VALUE, $this->sut->getData()[static::RADIO_KEY] ?? null);
    }

    /**
     * @test
     *
     * @depends selectYesIsCallable
     * @depends getRadioInputReturnsAnInput
     */
    public function selectYesAddsRadioToInputFilterData(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->selectYes();
        $result->isValid();

        // Assert
        $this->assertSame(static::YES_VALUE_OPTION_VALUE, $this->sut->getRadioInput()->getValue('application-version'));
    }

    /**
     * @test
     */
    public function setApplicationVersionIsCallable(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'setApplicationVersion']);
    }

    /**
     * @test
     *
     * @depends setApplicationVersionIsCallable
     */
    public function setApplicationVersionReturnsSelf(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->setApplicationVersion(static::AN_APPLICATION_VERSION);

        // Assert
        $this->assertSame($this->sut, $result);
    }

    /**
     * @test
     *
     * @depends setApplicationVersionIsCallable
     */
    public function setApplicationVersionAddsApplicationVersionToData(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->setApplicationVersion(static::AN_APPLICATION_VERSION);
        $result->isValid();

        // Assert
        $this->assertSame(static::AN_APPLICATION_VERSION, $this->sut->getData()[static::APPLICATION_VERSION_KEY] ?? null);
    }

    /**
     * @test
     *
     * @depends setApplicationVersionIsCallable
     * @depends getApplicationVersionInputReturnsAnInput
     */
    public function setApplicationVersionAddsApplicationVersionToInputFilterData(): void
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->setApplicationVersion(static::AN_APPLICATION_VERSION);
        $result->isValid();

        // Assert
        $this->assertSame(static::AN_APPLICATION_VERSION, $this->sut->getApplicationVersionInput()->getValue('application-version'));
    }

    protected function setUpSut(string $name): void
    {
        $this->sut = new AddVehiclesQuestionForm($name);
    }
}
