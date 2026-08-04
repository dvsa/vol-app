<?php

/**
 * Gpw Element
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element as LaminasElement;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\I18n\Validator\Alnum;

/**
 * Gpw Element
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class Gpw extends LaminasElement implements InputProviderInterface
{
    public function __construct($name = null, $options = [])
    {
        parent::__construct($name, $options);
    }

    /**
     * Provide default input rules for this element.
     *
     * @return ((int[]|string)[][]|null|string|true)[]
     *
     * @psalm-return array{name: null|string, required: true, validators: list{array{name: Digits::class}, array{name: GreaterThan::class, options: array{min: 0}}}}
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => true,
            'validators' => [
                ['name' => \Laminas\Validator\Digits::class],
                ['name' => \Laminas\Validator\GreaterThan::class, 'options' => ['min' => 0]],
            ]
        ];
    }
}
