<?php

/**
 * Input Specification for Conviction offence details
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element\Textarea as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;

/**
 * Input Specification for Convition additional info
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class ConvictionTextarea extends LaminasElement implements InputProviderInterface
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
    }

    /**
     * Provide default input rules for this element.
     *
     * @return (array|bool|null|string)[]
     *
     * @psalm-return array{name: null|string, required: true, allow_empty: false, validators: array<never, never>}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'allow_empty' => false,
            'validators' => [
            ]
        ];
    }
}
