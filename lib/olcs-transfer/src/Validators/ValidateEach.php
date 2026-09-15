<?php

namespace Dvsa\Olcs\Transfer\Validators;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Exception\InvalidArgumentException;
use Laminas\Validator\IsCountable;
use Laminas\Validator\ValidatorChain;

class ValidateEach extends IsCountable
{
    public const OPTION_CHILDREN = 'children';
    public const ERROR_TEMPLATE_KEY_NOT_ARRAY = 'notArray';
    public const ERROR_TEMPLATE_NOT_ARRAY = 'Expected input to be an array';

    /**
     * @var ValidatorChain
     */
    protected $validatorChain;

    /**
     * @inheritDoc
     */
    public function __construct($options = null)
    {
        $this->messageTemplates[self::ERROR_TEMPLATE_KEY_NOT_ARRAY] = self::ERROR_TEMPLATE_NOT_ARRAY;
        parent::__construct($options);
        $this->validatorChain = $this->newChildValidatorChain();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function isValid($value)
    {
        if (! parent::isValid($value)) {
            return false;
        }
        foreach ($value as $itemKey => $itemValue) {
            if (! $this->validatorChain->isValid($itemValue)) {
                foreach ($this->validatorChain->getMessages() as $itemMessageKey => $itemMessage) {
                    $newMessageKey = sprintf('%s.%s', $itemKey, $itemMessageKey);
                    $this->abstractOptions['messageTemplates'][$newMessageKey] = $itemMessage;
                    $this->error($newMessageKey);
                }
            }
        }
        return empty($this->getMessages());
    }

    /**
     * Creates a new child validator instance.
     *
     * @return ValidatorChain
     */
    protected function newChildValidatorChain(): ValidatorChain
    {
        $validatorChain = new ValidatorChain();

        $children = $this->getOption(static::OPTION_CHILDREN);
        if (empty($children)) {
            throw new InvalidArgumentException(sprintf("Invalid option '%s': option should not be empty", static::OPTION_CHILDREN));
        }

        foreach ($children as $childConfig) {
            $child = new $childConfig['name']();
            assert($child instanceof AbstractValidator, 'Expected instance of AbstractValidator');
            $childOptions = $childConfig['options'] ?? [];
            if (is_array($childOptions) && !empty($childOptions)) {
                $child->setOptions($childOptions);
            }
            $validatorChain->attach($child);
        }
        return $validatorChain;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getMessages()
    {
        return $this->abstractOptions['messages'];
    }
}
