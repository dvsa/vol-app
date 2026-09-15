<?php

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;

/**
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @deprecated This should not be used and must be removed as part of OLCS-15198
 *             Replace other elements with the normal Text element provided by
 *             Laminas.
 */
class Text extends LaminasElement\Text implements InputProviderInterface
{
    protected $isRequired = false;

    protected $isAllowEmpty = true;

    protected $min = 2;

    protected $max;

    /**
     * Text constructor.
     *
     * @param null  $name    Name
     * @param array $options Options
     */
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
    }

    /**
     * Get a list of validators
     *
     * @return array
     */
    protected function getValidators()
    {
        $validators = [];

        if (!empty($this->max) || !empty($this->min)) {
            $validators[] = [
                'name' => \Laminas\Validator\StringLength::class,
                'options' => array_filter(
                    [
                        'min' => $this->min,
                        'max' => $this->max,
                    ]
                ),
            ];
        }

        if ($this->isAllowEmpty === true) {
            $validators[] = [
                'name' => \Laminas\Validator\NotEmpty::class,
                'options' => [
                    'type' => \Laminas\Validator\NotEmpty::PHP,
                ],
            ];
        }

        return $validators;
    }

    /**
     * Setter for allow empty
     *
     * @param boolean $isAllowEmpty Is Allow empty
     *
     * @return $this
     */
    public function setAllowEmpty($isAllowEmpty)
    {
        $this->isAllowEmpty = (bool)$isAllowEmpty;
        return $this;
    }

    /**
     * Setter for max
     *
     * @param int $max Max
     *
     * @return $this
     */
    public function setMax($max)
    {
        $this->max = $max;
        return $this;
    }

    /**
     * Setter for min
     *
     * @param int $min Min
     *
     * @return $this
     */
    public function setMin($min)
    {
        $this->min = $min;
        return $this;
    }

    /**
     * Provide default input rules for this element.
     *
     * @return (array|mixed|null|string)[]
     *
     * @psalm-return array{name: null|string, required: mixed, filters: list{array{name: \Laminas\Filter\StringTrim::class}}, validators: array}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => $this->isRequired,
            'filters' => [
                [
                    'name' => \Laminas\Filter\StringTrim::class,
                ],
            ],
            'validators' => $this->getValidators(),
        ];
    }
}
