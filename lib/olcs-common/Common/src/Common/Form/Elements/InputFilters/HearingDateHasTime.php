<?php

/**
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 *
 */

namespace Common\Form\Elements\InputFilters;

use Laminas\Form\Element\DateSelect as LaminasDateSelect;
use Laminas\Validator as LaminasValidator;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Date as DateValidator;

/**
 * Checks conviction offence date is before the conviction date
 */
class HearingDateHasTime extends LaminasDateSelect implements InputProviderInterface
{
    /**
     * Provide default input rules for this element.
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        return [
            'name' => $this->getName(),
            'required' => false,
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
            'validators' => [
                new DateValidator(['format' => 'Y-m-d']),
                new \Common\Form\Elements\Validators\DateWithTime('hearingTime')
            ]
        ];
    }
}
