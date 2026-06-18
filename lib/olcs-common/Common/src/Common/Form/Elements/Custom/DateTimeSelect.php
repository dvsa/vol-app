<?php

/**
 * DateTimeSelect
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Common\Form\Elements\Custom;

use Laminas\Form\Element as LaminasElement;
use Laminas\Form\Exception\InvalidArgumentException;

/**
 * DateTimeSelect
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class DateTimeSelect extends LaminasElement\DateTimeSelect
{
    use Traits\YearDelta;

    /**
     * Should return an array specification compatible with
     * {@link Laminas\InputFilter\Factory::createInput()}.
     */
    #[\Override]
    public function getInputSpecification(): array
    {
        if ($this->getOption('max_year_delta')) {
            $maxYear = date('Y', strtotime($this->getOption('max_year_delta') . ' years'));

            // the minimum year is either:
            // a) the input value's year, if less than the current year
            // b) the current year if it has no value or it's a forthcoming year
            $refStamp = strtotime($this->getValue());
            $currentYear = date('Y');

            if ($refStamp !== false) {
                $refYear = date('Y', $refStamp);
                if ($refYear > $currentYear) {
                    $refYear = $currentYear;
                }
            } else {
                $refYear = $currentYear;
            }

            $this->setMinYear($refYear);
            $this->setMaxYear($maxYear);
        }

        return [
            'name' => $this->getName(),
            'required' => $this->getOption('required'),
            'filters' => [
                [
                    'name'    => 'Callback',
                    'options' => [
                        'callback' => static function ($date) {
                            // Convert the date to a specific format
                            if (is_array($date)) {
                                if (!isset($date['second'])) {
                                    $date['second'] = '00';
                                }

                                $date = sprintf(
                                    '%s-%s-%s %s:%s:%s',
                                    $date['year'],
                                    $date['month'],
                                    $date['day'],
                                    $date['hour'],
                                    $date['minute'],
                                    $date['second']
                                );
                            }
                            return $date;
                        }
                    ]
                ]
            ],
            'validators' => [
                $this->getValidator(),
            ]
        ];
    }

    /**
     * Overrides the default Laminas behaviour if the value is null
     *
     * @param mixed $value Date time value to set
     */
    #[\Override]
    public function setValue($value): void
    {
        if (null === $value) {
            $this->yearElement->setValue(null);
            $this->monthElement->setValue(null);
            $this->dayElement->setValue(null);
            $this->hourElement->setValue(null);
            $this->minuteElement->setValue(null);
            $this->secondElement->setValue(null);
        } else {
            // value could have a timezone offset in it, therefore convert time to app local time
            if (is_string($value)) {
                try {
                    $value = new \DateTime($value);
                } catch (\Exception) {
                    throw new InvalidArgumentException('Value should be a parsable string or an instance of \DateTime');
                }
            }

            if ($value instanceof \DateTime) {
                $value->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            }

            parent::setValue($value);
        }
    }
}
