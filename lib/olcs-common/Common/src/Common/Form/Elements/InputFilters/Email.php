<?php

/**
 * Email Filter
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element as LaminasElement;
use Laminas\Validator as LaminasValidator;
use Laminas\InputFilter\InputProviderInterface;

/**
 * Email Filter
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class Email extends LaminasElement implements InputProviderInterface
{
    /**
     * Provide default input rules for this element.
     *
     * @return ((int[]|string)[][]|false|null|string)[]
     *
     * @psalm-return array{name: null|string, required: false, filters: list{array{name: \Laminas\Filter\StringTrim::class}}, validators: list{array{name: \Dvsa\Olcs\Transfer\Validators\EmailAddress::class}, array{name: \Laminas\Validator\StringLength::class, options: array{min: 5, max: 255}}}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => false,
            'filters' => [
                ['name' => \Laminas\Filter\StringTrim::class],
            ],
            'validators' => [
                // @NOTE don't know if this is still used but I'll update it anyway
                ['name' => \Dvsa\Olcs\Transfer\Validators\EmailAddress::class],
                ['name' => \Laminas\Validator\StringLength::class, 'options' => ['min' => 5, 'max' => 255]],
            ]
        ];
    }
}
