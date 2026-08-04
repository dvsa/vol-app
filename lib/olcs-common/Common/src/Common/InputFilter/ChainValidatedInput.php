<?php

declare(strict_types=1);

namespace Common\InputFilter;

use Laminas\InputFilter\InputInterface;
use Laminas\Filter\FilterChain;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\NotEmpty;
use Laminas\InputFilter\Input;
use InvalidArgumentException;

/**
 * @see \CommonTest\InputFilter\ChainValidatedInputTest
 */
class ChainValidatedInput implements InputInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $breakOnFailure = false;

    /**
     * @var string|null
     */
    protected $errorMessage;

    /**
     * @var FilterChain|null
     */
    protected $filterChain;

    /**
     * @var bool
     */
    protected $required = true;

    /**
     * @var ValidatorChain|null
     */
    protected $validatorChain;

    /**
     * @var mixed
     */
    protected $value;

    public function __construct(string $name)
    {
        $this->validatorChain = new ValidatorChain();
        $this->filterChain = new FilterChain();
        $this->setName($name);
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string $name
     */
    #[\Override]
    public function setName($name): self
    {
        if (! is_string($name)) {
            throw new InvalidArgumentException('Expected string');
        }

        $this->name = $name;
        return $this;
    }

    #[\Override]
    public function getValidatorChain(): ValidatorChain|null
    {
        return $this->validatorChain;
    }

    #[\Override]
    public function setValidatorChain(ValidatorChain $validatorChain): self
    {
        $this->validatorChain = $validatorChain;
        return $this;
    }

    /**
     * @deprecated Add NotEmpty validator to the ValidatorChain and check for its presence
     */
    #[\Override]
    public function allowEmpty(): bool
    {
        return $this->validatorChainHasNotEmptyValidator($this->getValidatorChain());
    }

    /**
     * @param  bool $allowEmpty
     * @deprecated Add NotEmpty validator to the ValidatorChain
     */
    #[\Override]
    public function setAllowEmpty($allowEmpty): self
    {
        if (! is_bool($allowEmpty)) {
            throw new InvalidArgumentException('Expected bool');
        }

        $validatorChain = $this->getValidatorChain();
        if ($allowEmpty) {
            $validatorChain = $this->extractValidatorChainWithoutNotEmptyValidators($validatorChain);
        } elseif (! $this->validatorChainHasNotEmptyValidator($validatorChain)) {
            $validatorChain->attach(new NotEmpty());
        }

        $this->setValidatorChain($validatorChain);

        return $this;
    }

    protected function extractValidatorChainWithoutNotEmptyValidators(ValidatorChain $unfilteredValidatorChain): ValidatorChain
    {
        $filteredValidatorChain = new ValidatorChain();
        $filteredValidatorChain->setPluginManager($unfilteredValidatorChain->getPluginManager());
        foreach ($unfilteredValidatorChain->getValidators() as $validatorConfig) {
            if ($validatorConfig['instance'] instanceof NotEmpty) {
                continue;
            }

            $filteredValidatorChain->attach(
                $validatorConfig['instance'],
                $validatorConfig['breakChainOnFailure'] ?? false,
                $validatorConfig['priority'] ?? ValidatorChain::DEFAULT_PRIORITY
            );
        }

        return $filteredValidatorChain;
    }

    protected function validatorChainHasNotEmptyValidator(ValidatorChain $validatorChain): bool
    {
        foreach ($this->getValidatorChain()->getValidators() as $validatorConfig) {
            if ($validatorConfig['instance'] instanceof NotEmpty) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     *
     * This has no effect on the validation chain.
     */
    #[\Override]
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * {@inheritDoc}
     *
     * This has no effect on the validation chain.
     *
     * @param  bool $required
     */
    #[\Override]
    public function setRequired($required): self
    {
        if (! is_bool($required)) {
            throw new InvalidArgumentException('Expected bool');
        }

        $this->required = $required;
        return $this;
    }

    #[\Override]
    public function breakOnFailure(): bool
    {
        return $this->breakOnFailure;
    }

    /**
     * @param  bool $breakOnFailure
     */
    #[\Override]
    public function setBreakOnFailure($breakOnFailure): self
    {
        if (! is_bool($breakOnFailure)) {
            throw new InvalidArgumentException('Expected bool');
        }

        $this->breakOnFailure = $breakOnFailure;
        return $this;
    }

    #[\Override]
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @param  string|null $errorMessage
     */
    #[\Override]
    public function setErrorMessage($errorMessage): self
    {
        $this->errorMessage = null === $errorMessage ? null : (string) $errorMessage;
        return $this;
    }

    #[\Override]
    public function getFilterChain(): FilterChain|null
    {
        return $this->filterChain;
    }

    #[\Override]
    public function setFilterChain(FilterChain $filterChain): self
    {
        $this->filterChain = $filterChain;
        return $this;
    }

    /**
     * @return mixed
     */
    #[\Override]
    public function getRawValue()
    {
        return $this->value;
    }

    /**
     * @return mixed
     */
    #[\Override]
    public function getValue()
    {
        return $this->getFilterChain()->filter($this->value);
    }

    /**
     * Set the input value.
     *
     * @param  mixed $value
     */
    #[\Override]
    public function setValue($value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @param  mixed $context Extra "context" to provide the validator
     */
    #[\Override]
    public function isValid($context = null): bool
    {
        return $this->getValidatorChain()->isValid($this->getValue(), $context);
    }

    /**
     * @return string[]
     */
    #[\Override]
    public function getMessages(): array
    {
        return null === $this->errorMessage ? $this->getValidatorChain()->getMessages() : [$this->errorMessage];
    }

    #[\Override]
    public function merge(InputInterface $input): self
    {
        $this->setBreakOnFailure($input->breakOnFailure());
        $this->setErrorMessage($input->getErrorMessage());
        $this->setName($input->getName());
        $this->setRequired($input->isRequired());
        if (! $input instanceof Input || $input->hasValue()) {
            $this->setValue($input->getRawValue());
        }

        $this->getFilterChain()->merge($input->getFilterChain());
        $this->getValidatorChain()->merge($input->getValidatorChain());
        return $this;
    }
}
