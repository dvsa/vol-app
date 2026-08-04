<?php

namespace Common\Form\Elements\Custom;

use Laminas\Form\Element as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Regex as RegexValidator;
use Common\Form\Elements\Validators\NoOfPermitsMin as NoOfPermitsMinValidator;
use Common\Form\Elements\Validators\NoOfPermitsMax as NoOfPermitsMaxValidator;
use Common\Form\Elements\Validators\NoOfPermitsNotEmpty as NoOfPermitsNotEmptyValidator;

/**
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermits extends LaminasElement implements InputProviderInterface
{
    protected $attributes = [
        'type' => 'number',
    ];

    /**
     * @return ((NoOfPermitsMaxValidator|NoOfPermitsMinValidator|NoOfPermitsNotEmptyValidator|RegexValidator|string[])[]|null|string|true)[]
     *
     * @psalm-return array{type: \Laminas\InputFilter\Input::class, name: null|string, filters: list{array{name: \Laminas\Filter\StringTrim::class}}, required: true, validators: list{NoOfPermitsNotEmptyValidator, RegexValidator, NoOfPermitsMinValidator, NoOfPermitsMaxValidator}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'type' => \Laminas\InputFilter\Input::class,
            'name' => $this->getName(),
            'filters' => [
                ['name' => \Laminas\Filter\StringTrim::class]
            ],
            'required' => true,
            'validators' => [
                new NoOfPermitsNotEmptyValidator(),
                // note: this regex passes when negative numbers are passed in
                new RegexValidator('(^-?\d*(\.\d+)?$)'),
                new NoOfPermitsMinValidator(),
                new NoOfPermitsMaxValidator($this->attributes['max'])
            ]
        ];
    }

    /**
     * Returns true if the element has a non-zero value
     *
     * @return bool
     */
    public function hasNonZeroValue()
    {
        return $this->getValue() != 0;
    }
}
