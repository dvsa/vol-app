<?php

namespace Dvsa\Olcs\Transfer\Util;

use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputInterface;
use Laminas\Filter\FilterChain;
use Laminas\Validator\ValidatorChain;
use Laminas\InputFilter\CollectionInputFilter;
use Laminas\InputFilter\EmptyContextInterface;

/**
 * Structured Input (Kind of a mashup between an Input and InputFilter)
 * - Manages validation and filtering on nested inputs whilst also allowing validation and filtering on itself
 *
 * @template TFilteredValues
 * @implements InputFilterInterface<TFilteredValues>
 */
class StructuredInput implements InputInterface, InputFilterInterface
{
    /**
     * @var bool
     */
    protected $allowEmpty = false;

    /**
     * @var bool
     */
    protected $continueIfEmpty = false;

    /**
     * @var bool
     */
    protected $breakOnFailure = false;

    /**
     * @var string|null
     */
    protected $errorMessage;

    /**
     * @var FilterChain
     */
    protected $filterChain;

    /**
     * @var bool
     */
    protected $notEmptyValidator = false;

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var ValidatorChain
     */
    protected $validatorChain;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var mixed
     */
    protected $fallbackValue;

    /**
     * @var bool
     */
    protected $hasFallback = false;

    protected $inputs = [];

    protected $invalidInputs;

    protected $validationGroup;

    protected $validInputs;

    protected $messages;

    /**
     * Construct
     *
     * @param string $name Name
     *
     * @return void
     */
    public function __construct(protected $name = null)
    {
    }

    /**
     * Set allowEmpty
     *
     * @param bool $allowEmpty Allow empty
     *
     * @return static
     */
    #[\Override]
    public function setAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = $allowEmpty;
        return $this;
    }

    /**
     * Set breakOnFailure
     *
     * @param bool $breakOnFailure Break on failure
     *
     * @return static
     */
    #[\Override]
    public function setBreakOnFailure($breakOnFailure)
    {
        $this->breakOnFailure = $breakOnFailure;
        return $this;
    }

    /**
     * Set errorMessage
     *
     * @param string|null $errorMessage Error message

     * @return static
     */
    #[\Override]
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * Set filterChain
     *
     * @param \Laminas\Filter\FilterChain $filterChain Filter chain
     *
     * @return static
     */
    #[\Override]
    public function setFilterChain(FilterChain $filterChain)
    {
        $this->filterChain = $filterChain;
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name Name
     *
     * @return static
     */
    #[\Override]
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set required
     *
     * @param bool $required Required
     *
     * @return static
     */
    #[\Override]
    public function setRequired($required)
    {
        $this->required = $required;
        return $this;
    }

    /**
     * Set validatorChain
     *
     * @param ValidatorChain $validatorChain Validator chain
     *
     * @return static
     */
    #[\Override]
    public function setValidatorChain(ValidatorChain $validatorChain)
    {
        $this->validatorChain = $validatorChain;
        return $this;
    }

    /**
     * Set value
     *
     * @param mixed $value Value
     *
     * @return static
     */
    #[\Override]
    public function setValue($value)
    {
        $this->setData($value);
        return $this;
    }

    /**
     * Merge
     *
     * @param \Laminas\InputFilter\InputInterface $input Value
     *
     * @return static
     */
    #[\Override]
    public function merge(InputInterface $input)
    {
        return $this;
    }

    /**
     * Get allowEmpty
     *
     * @return bool
     */
    #[\Override]
    public function allowEmpty()
    {
        return $this->allowEmpty;
    }

    /**
     * Get breakOnFailure
     *
     * @return bool
     */
    #[\Override]
    public function breakOnFailure()
    {
        return $this->breakOnFailure;
    }

    /**
     * Get errorMessage
     *
     * @return string|null
     */
    #[\Override]
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Get filterChain
     *
     * @return \Laminas\Filter\FilterChain
     */
    #[\Override]
    public function getFilterChain()
    {
        return $this->filterChain;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    #[\Override]
    public function getName()
    {
        return $this->name;
    }

    /**
     * Is required
     *
     * @return bool
     */
    #[\Override]
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Get validatorChain
     *
     * @return ValidatorChain
     */
    #[\Override]
    public function getValidatorChain(): ValidatorChain
    {
        return $this->validatorChain;
    }

    /**
     * Add
     *
     * @param \Laminas\InputFilter\InputFilterInterface|\Laminas\InputFilter\InputInterface|\Traversable|array $input Input
     * @param string|null                                                                                  $name  Name
     * @return self
     */
    #[\Override]
    public function add($input, $name = null)
    {
        if ($name === null) {
            $name = $input->getName();
        }

        $this->inputs[$name] = $input;
        return $this;
    }

    /**
     * Get
     *
     */
    #[\Override]
    public function get($name)
    {
        return $this->inputs[$name];
    }

    /**
     * Has
     *
     * @param string $name Name
     *
     * @return bool
     */
    #[\Override]
    public function has($name): bool
    {
        return isset($this->inputs[$name]);
    }

    /**
     * Remove
     *
     * @param string $name Name
     *
     * @return self
     */
    #[\Override]
    public function remove($name)
    {
        unset($this->inputs[$name]);
        return $this;
    }

    /**
     * Set data
     *
     * @param mixed $data Data
     *
     * @return self
     */
    #[\Override]
    public function setData($data)
    {
        $this->data = $data;
        $this->populate();
        return $this;
    }

    /**
     * Is valid
     *
     * @param mixed $context Context
     *
     * @return bool
     */
    #[\Override]
    public function isValid($context = null): bool
    {
        if (empty($this->data) && !$this->isRequired()) {
            return true;
        }

        $validatorChain = $this->getValidatorChain();

        if ($validatorChain->isValid($this->getValue(), $context)) {
            return $this->validateInputs();
        }

        $this->messages = $validatorChain->getMessages();
        return false;
    }

    /**
     * Set validationGroup
     *
     * @param mixed $name Name
     *
     * @return self
     */
    #[\Override]
    public function setValidationGroup($name)
    {
        return $this;
    }

    /**
     * Get invalidInputs
     *
     * @return array<string, InputInterface|InputFilterInterface>
     */
    #[\Override]
    public function getInvalidInput()
    {
        return $this->invalidInputs;
    }

    /**
     * Get validInputs
     *
     * @return array<string, InputInterface|InputFilterInterface>
     */
    #[\Override]
    public function getValidInput()
    {
        return $this->validInputs;
    }

    /**
     * Get value
     *
     * @param string $name Name
     *
     * @return array|null
     */
    #[\Override]
    public function getValue($name = null): ?array
    {
        if ($name !== null) {
            return $this->inputs[$name]->getValue();
        }

        return $this->getValues();
    }

    /**
     * Get values
     *
     * @return array<string, mixed>|null
     */
    #[\Override]
    public function getValues(): ?array
    {
        if (empty($this->data)) {
            return null;
        }

        $values = [];

        foreach ($this->inputs as $name => $input) {
            $values[$name] = $input->getValue();
        }

        $filterChain = $this->getFilterChain();

        $values = $filterChain->filter($values);

        return $values;
    }

    /**
     * Get raw value
     *
     * @param string $name Name
     *
     * @return mixed
     */
    #[\Override]
    public function getRawValue($name = null)
    {
        if ($name !== null) {
            return $this->inputs[$name]->getRawValue();
        }

        return $this->getRawValues();
    }

    /**
     * Get raw values
     *
     * @return array
     */
    #[\Override]
    public function getRawValues()
    {
        $values = [];

        foreach ($this->inputs as $name => $input) {
            $values[$name] = $input->getRawValue();
        }

        return $values;
    }

    /**
     * Get messages
     * @return array<array-key, array<array-key, string|array>> | array<array-key, string> Error messages
     */
    #[\Override]
    public function getMessages()
    {
        if ($this->messages !== null) {
            return $this->messages;
        }

        $messages = [];
        foreach ($this->getInvalidInput() as $name => $input) {
            $messages[$name] = $input->getMessages();
        }

        return $messages;
    }

    /**
     * Count
     *
     * @return int
     */
    #[\Override]
    public function count(): int
    {
        return count($this->inputs);
    }

    /**
     * Populate the values of all attached inputs
     *
     * @return void
     */
    protected function populate()
    {
        foreach ($this->inputs as $name => $input) {
            if ($input instanceof CollectionInputFilter) {
                $input->clearValues();
                $input->clearRawValues();
            }

            if (!isset($this->data[$name])) {
                // No value; clear value in this input
                if ($input instanceof InputFilterInterface) {
                    $input->setData([]);
                    continue;
                }

                if ($input instanceof ArrayInput) {
                    $input->setValue([]);
                    continue;
                }

                $input->setValue(null);
                continue;
            }

            $value = $this->data[$name];

            if ($input instanceof InputFilterInterface) {
                $input->setData($value);
                continue;
            }

            $input->setValue($value);
        }
    }

    /**
     * Validate a set of inputs against the current data
     *
     * @return bool
     */
    protected function validateInputs()
    {
        // backwards compatibility
        $data = $this->getValues();

        $this->validInputs   = [];
        $this->invalidInputs = [];
        $valid               = true;

        foreach ($this->inputs as $name => $input) {
            $dataExists = is_array($data) && array_key_exists($name, $data);

            // key doesn't exist, but input is not required; valid
            if (!$dataExists && $input instanceof InputInterface && !$input->isRequired()) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // key doesn't exist, input is required, allows empty; valid if
            // continueIfEmpty is false or input doesn't implement
            // that interface; otherwise validation chain continues
            if (!$dataExists && $input instanceof InputInterface && $input->isRequired() && $input->allowEmpty()) {
                if (!($input instanceof EmptyContextInterface && $input->continueIfEmpty())) {
                    $this->validInputs[$name] = $input;
                    continue;
                }
            }

            // key exists, is null, input is not required; valid
            if ($dataExists && null === $data[$name] && $input instanceof InputInterface && !$input->isRequired()) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // key exists, is null, input is required, allows empty; valid if
            // continueIfEmpty is false or input doesn't implement
            // that interface; otherwise validation chain continues
            if (
                $dataExists && null === $data[$name] && $input instanceof InputInterface && $input->isRequired()
                && $input->allowEmpty()
            ) {
                if (!($input instanceof EmptyContextInterface && $input->continueIfEmpty())) {
                    $this->validInputs[$name] = $input;
                    continue;
                }
            }

            // key exists, empty string, input is not required, allows empty; valid
            if (
                $dataExists && '' === $data[$name] && $input instanceof InputInterface && !$input->isRequired()
                && $input->allowEmpty()
            ) {
                $this->validInputs[$name] = $input;
                continue;
            }

            // key exists, empty string, input is required, allows empty; valid
            // if continueIfEmpty is false, otherwise validation continues
            if (
                $dataExists && '' === $data[$name] && $input instanceof InputInterface && $input->isRequired()
                && $input->allowEmpty()
            ) {
                if (!($input instanceof EmptyContextInterface && $input->continueIfEmpty())) {
                    $this->validInputs[$name] = $input;
                    continue;
                }
            }

            // make sure we have a value (empty) for validation
            if (!$dataExists) {
                $data[$name] = null;
            }

            // Validate an input filter
            if ($input instanceof InputFilterInterface) {
                if (!$input->isValid()) {
                    $this->invalidInputs[$name] = $input;
                    $valid = false;
                    continue;
                }
                $this->validInputs[$name] = $input;
                continue;
            }

            // Validate an input
            if ($input instanceof InputInterface) {
                if (!$input->isValid()) {
                    // Validation failure
                    $this->invalidInputs[$name] = $input;
                    $valid = false;

                    if ($input->breakOnFailure()) {
                        return false;
                    }
                    continue;
                }
                $this->validInputs[$name] = $input;
                continue;
            }
        }

        return $valid;
    }
}
