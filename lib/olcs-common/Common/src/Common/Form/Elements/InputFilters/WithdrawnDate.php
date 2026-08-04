<?php

/**
 * Checks that if a withdrawn checkbox is ticked then the corresponding date is also filled in
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\InputFilter\InputProviderInterface;
use Laminas\Form\Element\DateSelect as LaminasDateSelect;

/**
 * Checks that if a withdrawn checkbox is ticked then the corresponding date is also filled in
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class WithdrawnDate extends LaminasDateSelect implements InputProviderInterface
{
    /**
     * Provide default input rules for this element.
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'continue_if_empty' => true,
            'filters' => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => static function ($date) {
                            // Convert the date to a specific format
                            if (
                                !is_array($date) || empty($date['year']) ||
                                empty($date['month']) || empty($date['day'])
                            ) {
                                return null;
                            }
                            return $date['year'] . '-' . $date['month'] . '-' . $date['day'];
                        }
                    ]
                ]
            ],
            'validators' => $this->getValidators()
        ];
    }

    /**
     * @return \Common\Form\Elements\Validators\WithdrawnDate[]
     *
     * @psalm-return list{\Common\Form\Elements\Validators\WithdrawnDate}
     */
    public function getValidators(): array
    {
        return [
            new \Common\Form\Elements\Validators\WithdrawnDate()
        ];
    }
}
