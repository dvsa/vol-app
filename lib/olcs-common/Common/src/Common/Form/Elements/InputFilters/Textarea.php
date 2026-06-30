<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element\Textarea as LaminasElement;
use Laminas\Validator as LaminasValidator;
use Laminas\InputFilter\InputProviderInterface;

/**
 * @deprecated This only gets used once in \Olcs\Form\Model\Fieldset\ReverseTransactionDetails
 *             We must look into removing it and replacing with standard MultiCheckbox.
 *             Reference: OLCS-15198
 *
 * Textarea
 */
class Textarea extends LaminasElement implements InputProviderInterface
{
    protected $continueIfEmpty = false;

    protected $allowEmpty = false;

    protected $required = false;

    protected $max;

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
        $specification = [
            'name' => $this->getName(),
            'required' => $this->required,
            'continue_if_empty' => $this->continueIfEmpty,
            'allow_empty' => $this->allowEmpty,
            'filters' => [
                ['name' => \Laminas\Filter\StringTrim::class]
            ],
        ];

        if (!empty($this->max)) {
            $specification['validators'][] = [
                'name' => \Laminas\Validator\StringLength::class,
                'options' => ['min' => 5, 'max' => $this->max]
            ];
        }

        return $specification;
    }
}
