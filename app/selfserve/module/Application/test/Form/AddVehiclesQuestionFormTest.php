<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Form;

use Common\Test\MockeryTestCase;
use Laminas\Form\Element\Csrf;
use Common\Form\Element\Button;
use Laminas\Validator\InArray;
use Common\Form\Elements\Custom\RadioVertical;
use Laminas\Validator\NotEmpty;
use Laminas\Form\Element\Hidden;
use Laminas\InputFilter\InputInterface;

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
    protected const NO_VALUE_OPTION_LABEL= 'No';
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

    /**
     * @var AddVehiclesQuestionForm|null
     */
    protected $sut;

    /**
     * @test
     */
    public function __construct_InitialisesACsrfElement()
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
    public function __construct_InitialisesANextButtonElement()
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
    public function __construct_InitialisesAReturnToOverviewButtonElement()
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
    public function __construct_InitialisesARadioElement()
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
    public function __construct_InitialisesAnApplicationVersionElement()
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
    public function getNextButtonElement_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getNextButtonElement']);
    }

    /**
     * @test
     * @depends getNextButtonElement_IsCallable
     * @depends __construct_InitialisesANextButtonElement
     */
    public function getNextButtonElement_ReturnsAnInstanceOfButton()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertInstanceOf(Button::class, $this->sut->getNextButtonElement());
    }

    /**
     * @test
     * @depends getNextButtonElement_ReturnsAnInstanceOfButton
     */
    public function getNextButtonElement_ReturnsAnInstanceOfButton_WithAName()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertEquals(static::NEXT_BUTTON_NAME, $this->sut->getNextButtonElement()->getName());
    }

    /**
     * @test
     * @depends getNextButtonElement_ReturnsAnInstanceOfButton
     */
    public function getNextButtonElement_ReturnsAnInstanceOfButton_WithAValue()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertEquals(static::NEXT_BUTTON_VALUE, $this->sut->getNextButtonElement()->getValue());
    }

    /**
     * @test
     * @depends getNextButtonElement_ReturnsAnInstanceOfButton
     */
    public function getNextButtonElement_ReturnsAnInstanceOfButton_WithALabel()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertEquals(static::NEXT_BUTTON_LABEL, $this->sut->getNextButtonElement()->getLabel());
    }

    /**
     * @test
     */
    public function getReturnToOverviewButtonElement_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getReturnToOverviewButtonElement']);
    }

    /**
     * @test
     * @depends __construct_InitialisesAReturnToOverviewButtonElement
     */
    public function getReturnToOverviewButtonElement_ReturnsAnInstanceOfButton()
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
     * @depends getReturnToOverviewButtonElement_ReturnsAnInstanceOfButton
     */
    public function getReturnToOverviewButtonElement_ReturnsAnInstanceOfButton_WithAName()
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
     * @depends getReturnToOverviewButtonElement_ReturnsAnInstanceOfButton
     */
    public function getReturnToOverviewButtonElement_ReturnsAnInstanceOfButton_WithAValue()
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
     * @depends getReturnToOverviewButtonElement_ReturnsAnInstanceOfButton
     */
    public function getReturnToOverviewButtonElement_ReturnsAnInstanceOfButton_WithALabel()
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
    public function getRadioElement_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getRadioElement']);
    }

    /**
     * @test
     * @depends getRadioElement_IsCallable
     * @depends __construct_InitialisesARadioElement
     */
    public function getRadioElement_ReturnsARadio()
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
     * @depends getRadioElement_ReturnsARadio
     */
    public function getRadioElement_ReturnsARadio_WithName()
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
     * @depends getRadioElement_ReturnsARadio
     */
    public function getRadioElement_ReturnsARadio_WithLabel()
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
     * @depends getRadioElement_ReturnsARadio
     */
    public function getRadioElement_ReturnsARadio_WithHintOption()
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
     * @depends getRadioElement_ReturnsARadio
     */
    public function getRadioElement_ReturnsARadio_WithYesOption()
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
     * @depends getRadioElement_ReturnsARadio_WithYesOption
     */
    public function getRadioElement_ReturnsARadio_WithYesOption_ThatIsNotSelected()
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
     * @depends getRadioElement_ReturnsARadio_WithYesOption
     */
    public function getRadioElement_ReturnsARadio_WithYesOption_WithLabel()
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
     * @depends getRadioElement_ReturnsARadio
     */
    public function getRadioElement_ReturnsARadio_WithYesOption_WithValue()
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
     * @depends getRadioElement_ReturnsARadio
     */
    public function getRadioElement_ReturnsARadio_WithNoOption()
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
     * @depends getRadioElement_ReturnsARadio_WithNoOption
     */
    public function getRadioElement_ReturnsARadio_WithNoOption_ThatIsNotSelected()
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
     * @depends getRadioElement_ReturnsARadio_WithNoOption
     */
    public function getRadioElement_ReturnsARadio_WithNoOption_WithLabel()
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
     * @depends getRadioElement_ReturnsARadio_WithNoOption
     */
    public function getRadioElement_ReturnsARadio_WithNoOption_WithValue()
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
     * @depends getRadioElement_ReturnsARadio_WithNoOption
     */
    public function getRadioElement_ReturnsARadio_WithNoOption_WithConditionalContent()
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
    public function getSubmitInput_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getSubmitInput']);
    }

    /**
     * @test
     * @depends getSubmitInput_IsCallable
     */
    public function getSubmitInput_ReturnsInstanceOfInputInterface()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertInstanceOf(InputInterface::class, $this->sut->getSubmitInput());
    }

    /**
     * @test
     * @depends getSubmitInput_ReturnsInstanceOfInputInterface
     */
    public function getSubmitInput_ReturnsInstanceOfInputInterface_ThatIsRequired()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $this->sut->setData(static::EMPTY_ARRAY_VALUE);
        $this->sut->isValid();

        // Assert
        $this->assertNotNull($this->sut->getMessages()[static::SUBMIT_KEY] ?? null);
    }

    /**
     * @test
     * @depends getSubmitInput_ReturnsInstanceOfInputInterface
     */
    public function getSubmitInput_ReturnsInstanceOfInputInterface_ThatRejectsAnInvalidValue()
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
     * @depends getSubmitInput_ReturnsInstanceOfInputInterface_ThatRejectsAnInvalidValue
     */
    public function getSubmitInput_ReturnsInstanceOfInputInterface_ThatRejectsAnInvalidValue_WithACustomMessage()
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
    public function getRadioInput_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getRadioInput']);
    }

    /**
     * @test
     * @depends getRadioInput_IsCallable
     * @depends __construct_InitialisesARadioElement
     */
    public function getRadioInput_ReturnsInput()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getRadioInput();

        // Assert
        $this->assertInstanceOf(InputInterface::class, $result);
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
     * @depends getRadioInput_ReturnsInput
     * @dataProvider validSubmitValuesDataProvider
     */
    public function getRadioInput_ReturnsInputFilter_ThatAcceptsValidValues(string $value)
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
     * @depends getRadioInput_ReturnsInput
     */
    public function getRadioInput_ReturnsInputFilter_ThatIsRequired()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $this->sut->setData(static::EMPTY_ARRAY_VALUE);
        $this->sut->isValid();

        // Assert
        $this->assertNotNull($this->sut->getMessages()[static::RADIO_KEY] ?? null);
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
     * @param mixed $value
     * @test
     * @depends getRadioInput_ReturnsInput
     * @dataProvider invalidRadioValueDataProvider
     */
    public function getRadioInput_ReturnsInputFilter_ThatRejectsAnInvalidValue($value)
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
     * @depends getRadioInput_ReturnsInput
     */
    public function getRadioInput_ReturnsInputFilter_ThatRejectsAnInvalidValue_WithCustomMessageFor_NotEmpty_IsEmptyRule()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $validator = $this->sut->getRadioInput()->getValidatorChain()->getValidators()[0]['instance'];
        assert($validator instanceof NotEmpty);
        $result = $validator->getMessageTemplates()[NotEmpty::IS_EMPTY];

        // Assert
        $this->assertEquals(static::INVALID_RADIO_VALIDATION_MESSAGE, $result);
    }

    /**
     * @test
     * @depends getRadioInput_ReturnsInput
     */
    public function getRadioInput_ReturnsInputFilter_ThatRejectsAnInvalidValue_WithCustomMessageFor_NotEmpty_InvalidRule()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $validator = $this->sut->getRadioInput()->getValidatorChain()->getValidators()[0]['instance'];
        assert($validator instanceof NotEmpty);
        $result = $validator->getMessageTemplates()[NotEmpty::INVALID];

        // Assert
        $this->assertEquals(static::INVALID_RADIO_VALIDATION_MESSAGE, $result);
    }

    /**
     * @test
     * @depends getRadioInput_ReturnsInput
     */
    public function getRadioInput_ReturnsInputFilter_ThatRejectsAnInvalidValue_WithCustomMessageFor_InArray_NotInArrayRule()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $validator = $this->sut->getRadioInput()->getValidatorChain()->getValidators()[1]['instance'];
        assert($validator instanceof InArray);
        $result = $validator->getMessageTemplates()[InArray::NOT_IN_ARRAY];

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
    public function isValid_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'isValid']);
    }

    /**
     * @test
     */
    public function userHasOptedToContinueToTheNextStep_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'userHasOptedToContinueToTheNextStep']);
    }

    /**
     * @test
     * @depends userHasOptedToContinueToTheNextStep_IsCallable
     * @depends __construct_InitialisesANextButtonElement
     * @depends isValid_IsCallable
     */
    public function userHasOptedToContinueToTheNextStep_ReturnsTrueWhenSubmitValueIsNext()
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
     * @depends userHasOptedToContinueToTheNextStep_IsCallable
     * @depends __construct_InitialisesANextButtonElement
     * @depends isValid_IsCallable
     */
    public function userHasOptedToContinueToTheNextStep_ReturnsFalseWhenSubmitValueIsOverview()
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
     * @depends userHasOptedToContinueToTheNextStep_IsCallable
     * @depends __construct_InitialisesANextButtonElement
     * @depends isValid_IsCallable
     */
    public function userHasOptedToContinueToTheNextStep_ReturnsFalseWhenSubmitValueIsInvalid()
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
    public function userHasOptedToSubmitVehicleDetails_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'userHasOptedToSubmitVehicleDetails']);
    }

    /**
     * @param mixed $yesValue
     * @test
     * @depends userHasOptedToSubmitVehicleDetails_IsCallable
     * @depends __construct_InitialisesARadioElement
     * @depends isValid_IsCallable
     * @dataProvider yesRadioValidValuesDataProvider
     */
    public function userHasOptedToSubmitVehicleDetails_ReturnsTrueWhenRadioValueIsYes($yesValue)
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
     * @param mixed $noValue
     * @test
     * @depends userHasOptedToSubmitVehicleDetails_IsCallable
     * @depends __construct_InitialisesARadioElement
     * @depends isValid_IsCallable
     * @dataProvider noRadioValidValuesDataProvider
     */
    public function userHasOptedToSubmitVehicleDetails_ReturnsFalseWhenRadioValueIsNo($noValue)
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
     * @depends userHasOptedToSubmitVehicleDetails_IsCallable
     * @depends __construct_InitialisesARadioElement
     * @depends isValid_IsCallable
     */
    public function userHasOptedToSubmitVehicleDetails_ReturnsFalseWhenRadioValueIsInvalid()
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
    public function userHasOptedNotToSubmitVehicleDetails_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'userHasOptedNotToSubmitVehicleDetails']);
    }

    /**
     * @param mixed $noValue
     * @test
     * @depends userHasOptedNotToSubmitVehicleDetails_IsCallable
     * @depends __construct_InitialisesARadioElement
     * @depends isValid_IsCallable
     * @dataProvider noRadioValidValuesDataProvider
     */
    public function userHasOptedNotToSubmitVehicleDetails_ReturnsTrueWhenRadioValueIsNo($noValue)
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
     * @param mixed $yesValue
     * @test
     * @depends userHasOptedNotToSubmitVehicleDetails_IsCallable
     * @depends __construct_InitialisesARadioElement
     * @depends isValid_IsCallable
     * @dataProvider yesRadioValidValuesDataProvider
     */
    public function userHasOptedNotToSubmitVehicleDetails_ReturnsFalseWhenRadioValueIsYes($yesValue)
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
     * @depends userHasOptedNotToSubmitVehicleDetails_IsCallable
     * @depends __construct_InitialisesARadioElement
     * @depends isValid_IsCallable
     */
    public function userHasOptedNotToSubmitVehicleDetails_ReturnsFalseWhenRadioValueIsInvalid()
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
    public function getApplicationVersionElement_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getApplicationVersionElement']);
    }

    /**
     * @test
     * @depends getApplicationVersionElement_IsCallable
     * @depends __construct_InitialisesAnApplicationVersionElement
     */
    public function getApplicationVersionElement_ReturnsInstanceOfHidden()
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
    public function getApplicationVersionInput_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'getApplicationVersionInput']);
    }

    /**
     * @test
     * @depends getApplicationVersionInput_IsCallable
     */
    public function getApplicationVersionInput_ReturnsAnInstanceOfInput()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getApplicationVersionInput();

        // Assert
        $this->assertInstanceOf(InputInterface::class, $result);
    }

    /**
     * @return array
     */
    public function validApplicationVersionsDataProvider(): array
    {
        return [
            'positive integer' => [static::POSITIVE_INTEGER, static::POSITIVE_INTEGER],
            'positive integer string' => [static::POSITIVE_INTEGER_STRING, static::POSITIVE_INTEGER],
            'positive float' => [1.0, 1],
            'positive float string' => ['1.0', 1],
            'decimal positive float' => [1.1, 1],
            'decimal positive float string' => ['1.1', 1],
        ];
    }

    /**
     * @param mixed $value
     * @param mixed $filteredValue
     * @test
     * @depends getApplicationVersionInput_ReturnsAnInstanceOfInput
     * @dataProvider  validApplicationVersionsDataProvider
     */
    public function getApplicationVersionInput_FiltersValidValuesToBeIntegers($value, $filteredValue)
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getApplicationVersionInput();
        $result->setValue($value);

        // Assert
        $this->assertEquals($filteredValue, $result->getValue());
    }

    /**
     * @param mixed $value
     * @test
     * @depends getApplicationVersionInput_FiltersValidValuesToBeIntegers
     * @dataProvider validApplicationVersionsDataProvider
     */
    public function getApplicationVersionInput_AcceptsValidValues($value)
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getApplicationVersionInput();
        $result->setValue($value);

        // Assert
        $this->assertTrue($result->isValid());
    }

    /**
     * @return array
     */
    public function invalidApplicationVersionsDataProvider(): array
    {
        return [
            'zero integer' => [0],
            'zero string' => ['0'],
            'zero float' => [0.0],
            'zero float string' => ['0.0'],
            'empty string' => [''],
            'empty array' => [[]],
            'null' => [null],
            'string' => ['string'],
        ];
    }

    /**
     * @param mixed $value
     * @test
     * @depends getApplicationVersionInput_ReturnsAnInstanceOfInput
     * @dataProvider invalidApplicationVersionsDataProvider
     */
    public function getApplicationVersionInput_RejectsInvalidValues($value)
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getApplicationVersionInput();
        $result->setValue($value);

        // Assert
        $this->assertFalse($result->isValid());
    }

    /**
     * @test
     * @depends getApplicationVersionInput_ReturnsAnInstanceOfInput
     */
    public function getApplicationVersionInput_IsRequired()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->getApplicationVersionInput()->isRequired();

        // Assert
        $this->assertTrue($result);
    }

    /**
     * @test
     */
    public function selectNo_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'selectNo']);
    }

    /**
     * @test
     * @depends selectNo_IsCallable
     */
    public function selectNo_ReturnsSelf()
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
     * @depends selectNo_IsCallable
     */
    public function selectNo_AddsRadioToData()
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
     * @depends selectNo_IsCallable
     * @depends getRadioInput_ReturnsInput
     */
    public function selectNo_AddsRadioToInputFilterData()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->selectNo();
        $result->isValid();

        // Assert
        $this->assertSame(static::NO_VALUE_OPTION_VALUE, $this->sut->getRadioInput()->getValue());
    }

    /**
     * @test
     */
    public function selectYes_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'selectYes']);
    }

    /**
     * @test
     * @depends selectYes_IsCallable
     */
    public function selectYes_ReturnsSelf()
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
     * @depends selectYes_IsCallable
     */
    public function selectYes_AddsRadioToData()
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
     * @depends selectYes_IsCallable
     * @depends getRadioInput_ReturnsInput
     */
    public function selectYes_AddsRadioToInputFilterData()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->selectYes();
        $result->isValid();

        // Assert
        $this->assertSame(static::YES_VALUE_OPTION_VALUE, $this->sut->getRadioInput()->getValue());
    }

    /**
     * @test
     */
    public function setApplicationVersion_IsCallable()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Assert
        $this->assertIsCallable([$this->sut, 'setApplicationVersion']);
    }

    /**
     * @test
     * @depends setApplicationVersion_IsCallable
     */
    public function setApplicationVersion_ReturnsSelf()
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
     * @depends setApplicationVersion_IsCallable
     */
    public function setApplicationVersion_AddsApplicationVersionToData()
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
     * @depends setApplicationVersion_IsCallable
     * @depends getApplicationVersionInput_ReturnsAnInstanceOfInput
     */
    public function setApplicationVersion_AddsApplicationVersionToInputFilterData()
    {
        // Setup
        $this->setUpSut(static::A_FORM_NAME);

        // Execute
        $result = $this->sut->setApplicationVersion(static::AN_APPLICATION_VERSION);
        $result->isValid();

        // Assert
        $this->assertSame(static::AN_APPLICATION_VERSION, $this->sut->getApplicationVersionInput()->getValue());
    }

    /**
     * @param string $name
     */
    protected function setUpSut(string $name)
    {
        $this->sut = new AddVehiclesQuestionForm($name);
    }
}
