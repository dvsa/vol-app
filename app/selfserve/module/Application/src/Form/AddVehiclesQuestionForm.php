<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Form;

use Laminas\Form\Form;
use Common\Form\Elements\Custom\RadioVertical;
use Common\Form\Element\SubmitButton;
use Common\Form\Element\Button;
use Laminas\InputFilter\InputFilterAwareInterface;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\InArray;
use Laminas\Form\Element\Hidden;
use Laminas\InputFilter\InputInterface;
use Common\Form\FormWithCsrfTrait;
use Common\Form\FormWithCsrfInterface;
use Common\InputFilter\ChainValidatedInput;
use Laminas\Validator\ValidatorChain;

/**
 * @see AddVehiclesQuestionFormTest
 */
class AddVehiclesQuestionForm extends Form implements InputFilterAwareInterface, FormWithCsrfInterface
{
    use FormWithCsrfTrait;

    protected const YES = 1;
    protected const NO = 0;
    protected const NEXT = 'next';
    protected const OVERVIEW = 'overview';
    protected const RADIO = 'radio';
    protected const SUBMIT = 'submit';
    protected const NEXT_BUTTON = 'btn_next';
    protected const OVERVIEW_BUTTON = 'btn_overview';
    protected const APPLICATION_VERSION = 'application-version';

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->setUseInputFilterDefaults(false);
        $this->setInputFilter(new InputFilter());
        $this->initialiseRadio();
        $this->initialiseSubmit();
        $this->initialiseCsrf();
        $this->initialiseApplicationVersion();
    }

    protected function initialiseSubmit()
    {
        // Build elements
        $nextButtonElement = new SubmitButton(static::NEXT_BUTTON, 'Next');
        $this->add($nextButtonElement);
        $nextButtonElement->setTheme(Button::PRIMARY);
        $nextButtonElement->setValue(static::NEXT);
        $nextButtonElement->setAttribute('name', static::SUBMIT);

        $overviewButtonElement = new SubmitButton(static::OVERVIEW_BUTTON, 'application.vehicle.add-details.action.save-and-return');
        $this->add($overviewButtonElement);
        $overviewButtonElement->setTheme(Button::TERTIARY);
        $overviewButtonElement->setValue(static::OVERVIEW);
        $overviewButtonElement->setAttribute('name', static::SUBMIT);

        // Build input filter
        $input = new ChainValidatedInput(static::SUBMIT);
        $inArrayValidator = new InArray();
        $inArrayValidator->setStrict(InArray::COMPARE_STRICT);
        $inArrayValidator->setHaystack([static::NEXT, static::OVERVIEW]);
        $inArrayValidator->setMessage('An error occurred, please try again', InArray::NOT_IN_ARRAY);
        $input->getValidatorChain()->attach($inArrayValidator);

        $this->getInputFilter()->add($input);
    }

    /**
     * @return SubmitButton
     */
    public function getNextButtonElement(): SubmitButton
    {
        return $this->get(static::NEXT_BUTTON);
    }

    /**
     * @return SubmitButton
     */
    public function getReturnToOverviewButtonElement(): SubmitButton
    {
        return $this->get(static::OVERVIEW_BUTTON);
    }

    /**
     * @return InputInterface
     */
    public function getSubmitInput(): InputInterface
    {
        return $this->getInputFilter()->get(static::SUBMIT);
    }

    protected function initialiseRadio()
    {
        $radioElement = new RadioVertical(static::RADIO);
        $radioElement->setLabel('application.vehicle.add-details.radio.label');
        $radioElement->setOption('hint', 'application.vehicle.add-details.radio.hint');
        $radioElement->setValueOptions([
            'yes' => [
                'value' => static::YES,
                'label' => 'Yes',
            ],
            'no' => [
                'value' => static::NO,
                'label' => 'No',
                'conditional_content' => 'application.vehicle.add-details.radio.option.no.conditional-content',
            ],
        ]);
        $this->add($radioElement);

        $input = new ChainValidatedInput(static::RADIO);

        $inArrayValidator = new InArray();
        $inArrayValidator->setStrict(InArray::COMPARE_STRICT);
        $inArrayValidator->setHaystack([static::NO, static::YES]);
        $inArrayValidator->setMessage('application.vehicle.add-details.radio.messages.not-in-array', InArray::NOT_IN_ARRAY);
        $input->getValidatorChain()->attach($inArrayValidator);

        $filterChain = $input->getFilterChain();
        $filterChain->attach(function ($value) {
            if (in_array($value, [static::YES, (string) static::YES, static::NO, (string) static::NO], true)) {
                return (int) $value;
            }
            return $value;
        });

        $this->getInputFilter()->add($input);
    }

    /**
     * @return RadioVertical
     */
    public function getRadioElement(): RadioVertical
    {
        return $this->get(static::RADIO);
    }

    /**
     * @return InputInterface
     */
    public function getRadioInput(): InputInterface
    {
        return $this->getInputFilter()->get(static::RADIO);
    }

    /**
     * @return $this
     */
    public function selectNo(): self
    {
        return $this->setDataAttribute(static::RADIO, static::NO);
    }

    /**
     * @return $this
     */
    public function selectYes(): self
    {
        return $this->setDataAttribute(static::RADIO, static::YES);
    }

    /**
     * Determines whether a user has opted to continue to the next step of an application.
     *
     * @return bool
     */
    public function userHasOptedToContinueToTheNextStep(): bool
    {
        return $this->getData()[static::SUBMIT] === static::NEXT;
    }

    /**
     * Determines whether a user has opted to submit vehicle details for an application.
     *
     * @return bool
     */
    public function userHasOptedToSubmitVehicleDetails(): bool
    {
        return $this->getData()[static::RADIO] === static::YES;
    }

    /**
     * Determines whether a user has opted to not to submit vehicle details for an application.
     *
     * @return bool
     */
    public function userHasOptedNotToSubmitVehicleDetails(): bool
    {
        return $this->getData()[static::RADIO] === static::NO;
    }

    protected function initialiseApplicationVersion()
    {
        // Build element
        $applicationVersionInput = new Hidden(static::APPLICATION_VERSION);
        $this->add($applicationVersionInput);

        // Build input
        $input = new ChainValidatedInput(static::APPLICATION_VERSION);
        $input->setRequired(false);
        $this->getInputFilter()->add($input);
    }

    /**
     * @return Hidden
     */
    public function getApplicationVersionElement(): Hidden
    {
        return $this->get(static::APPLICATION_VERSION);
    }

    /**
     * @return InputInterface
     */
    public function getApplicationVersionInput(): InputInterface
    {
        return $this->getInputFilter()->get(static::APPLICATION_VERSION);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setApplicationVersion($value): self
    {
        return $this->setDataAttribute(static::APPLICATION_VERSION, $value);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    protected function setDataAttribute(string $name, $value): self
    {
        $this->setData(array_merge($this->data ?? [], [$name => $value]));
        $this->getInputFilter()->setData($this->data);
        return $this;
    }
}
