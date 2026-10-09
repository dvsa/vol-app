<?php

namespace Dvsa\Olcs\Transfer\Util\Attribute;

use Attribute;
use Laminas\Form\Annotation\Validator as LaminasValidator;

#[Attribute(Attribute::TARGET_ALL | Attribute::IS_REPEATABLE)]
class Validator
{
    protected LaminasValidator $validator;

    public function __construct($name, array $options = [], ?bool $breakChainOnFailure = null, ?int $priority = null)
    {
        $this->validator = new LaminasValidator($name, $options, $breakChainOnFailure, $priority);
    }

    public function __call($name, $arguments)
    {
        return $this->validator->{$name}($arguments);
    }

    public function getName()
    {
        $spec = $this->validator->getValidatorSpecification();

        return $spec['name'];
    }

    public function getOptions()
    {
        $spec = $this->validator->getValidatorSpecification();

        if (empty($spec['options'])) {
            return null;
        }

        return $spec['options'];
    }
}
