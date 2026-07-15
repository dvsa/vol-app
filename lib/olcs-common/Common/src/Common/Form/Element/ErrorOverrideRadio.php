<?php

namespace Common\Form\Element;

use Laminas\Form\Element\Radio;

/**
 * Class ErrorOverrideRadio
 * @package Common\Form\Element
 */
class ErrorOverrideRadio extends Radio
{
    public const INPUT_CLASS_KEY = 'input_class';

    #[\Override]
    public function getInputSpecification(): array
    {
        $spec = parent::getInputSpecification();

        if (isset($this->options[self::INPUT_CLASS_KEY])) {
            $spec['type'] = $this->options[self::INPUT_CLASS_KEY];
        }

        return $spec;
    }
}
