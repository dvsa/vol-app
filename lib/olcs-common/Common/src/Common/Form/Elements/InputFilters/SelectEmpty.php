<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element\Select as LaminasElement;
use Laminas\Validator as LaminasValidator;
use Laminas\InputFilter\InputProviderInterface;

/**
 * @deprecated Unused Custom InputFilter.  Need to remove in OLCS-15198
 *
 * Select with empty allowed
 */
class SelectEmpty extends LaminasElement implements InputProviderInterface
{
    protected $required = false;

    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
    }

    /**
     * Provide default input rules for this element.
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'required' => $this->required,
            'validators' => [
                [
                    'name' => LaminasValidator\NotEmpty::class,
                    'options' => [
                        'type' => LaminasValidator\NotEmpty::EMPTY_ARRAY,
                    ],
                ],
            ]
        ];
    }
}
