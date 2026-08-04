<?php

namespace Common\Service\Validation;

use Common\Service\Validation\Result\ValidationFailed;
use Common\Service\Validation\Result\ValidationSuccessful;
use Laminas\Filter\Exception\RuntimeException;
use Laminas\InputFilter\InputInterface;

/**
 * Class ChainedValidator
 * @package Common\Service\Validation
 */
final class ChainedValidator
{
    /**
     * @var array
     */
    private $validationChains = [];

    public function addValidationChain(InputInterface $chain): void
    {
        $this->validationChains[] = $chain;
    }

    /**
     * @return array
     */
    public function getValidationChains()
    {
        return $this->validationChains;
    }

    /**
     * @param $command
     * @return \Common\Service\Validation\Result\Validation
     */
    public function validate(CommandInterface $command)
    {
        $context = $command->getArrayCopy();
        $value = $command->getValue();
        $outputs = [];

        foreach ($this->validationChains as $chain) {
            /** @var InputInterface $chain */
            $chain->setValue($value);

            try {
                $valid = $chain->isValid($context);
                $outputs[$chain->getName()] = $chain->getValue();
                $context[$chain->getName()] = $chain->getValue();
            } catch (RuntimeException $e) {
                return new ValidationFailed($command, [$e->getMessage()]);
            }

            if (!$valid) {
                return new ValidationFailed($command, $chain->getMessages());
            }

            $value = $chain->getValue();
        }

        return new ValidationSuccessful($command, array_pop($outputs), $outputs);
    }
}
