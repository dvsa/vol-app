<?php

/**
 * Renders a date time select element
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Common\Form\View\Helper;

use Laminas\Form\ElementInterface;
use Laminas\Form\View\Helper\FormDateTimeSelect as LaminasFormDateTimeSelect;
use DateTime;
use IntlDateFormatter;
use Laminas\Form\Element\DateTimeSelect as DateTimeSelectElement;
use Laminas\Form\Exception;
use Laminas\Form\View\Helper\FormDateSelect as FormDateSelectHelper;

/**
 * Renders a date time select element
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class FormDateTimeSelect extends LaminasFormDateTimeSelect
{
    private $inputHelper;

    private $format = '<div class="field inline-text"><label for="%s">%s</label>%s</div>';

    private $displayEveryMinute;

    /**
     * Render a date element that is composed of six selects
     *
     * @throws \Laminas\Form\Exception\InvalidArgumentException
     * @throws \Laminas\Form\Exception\DomainException
     */
    #[\Override]
    public function render(ElementInterface $element): string
    {
        $this->pattern = $element->getOption('pattern');

        if (!$element instanceof DateTimeSelectElement) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s requires that the element is of type Laminas\Form\Element\DateTimeSelect',
                    __METHOD__
                )
            );
        }

        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(
                sprintf(
                    '%s requires that the element has an assigned name; none discovered',
                    __METHOD__
                )
            );
        }

        $this->setDisplayEveryMinute((bool) $element->getOption('display_every_minute'));

        $shouldRenderDelimiters = $element->shouldRenderDelimiters();
        $selectHelper = $this->getSelectElementHelper();
        $pattern      = $this->parsePattern($shouldRenderDelimiters);

        $hourOptions   = $this->getHoursOptions($pattern['hour']);
        $minuteOptions = $this->getMinutesOptions($pattern['minute']);
        $secondOptions = $this->getSecondsOptions($pattern['second']);

        $dayElement    = $element->getDayElement();
        $monthElement  = $element->getMonthElement();
        $yearElement   = $element->getYearElement();
        $hourElement   = $element->getHourElement()->setValueOptions($hourOptions);
        $minuteElement = $element->getMinuteElement()->setValueOptions($minuteOptions);
        $secondElement = $element->getSecondElement()->setValueOptions($secondOptions);

        // set ids on each input field
        $dayElement->setAttribute('id', $element->getAttribute('id') . '_day');
        $monthElement->setAttribute('id', $element->getAttribute('id') . '_month');
        $yearElement->setAttribute('id', $element->getAttribute('id') . '_year');
        $hourElement->setAttribute('id', $element->getAttribute('id') . '_hour');
        $minuteElement->setAttribute('id', $element->getAttribute('id') . '_minute');
        $secondElement->setAttribute('id', $element->getAttribute('id') . '_second');

        if ($element->shouldCreateEmptyOption()) {
            $hourElement->setEmptyOption('');
            $minuteElement->setEmptyOption('');
            $secondElement->setEmptyOption('');
        }

        $data = [];
        $data[$pattern['day']]    = $this->renderDayInput($dayElement);
        $data[$pattern['month']]  = $this->renderMonthInput($monthElement);
        $data[$pattern['year']]   = $this->renderYearInput($yearElement);
        $data[$pattern['hour']]   = $selectHelper->render($hourElement);
        $data[$pattern['minute']] = $selectHelper->render($minuteElement);

        if ($element->shouldShowSeconds()) {
            $data[$pattern['second']]  = $selectHelper->render($secondElement);
        } else {
            unset($pattern['second']);
            if ($shouldRenderDelimiters) {
                unset($pattern[4]);
            }
        }

        $markup = '';
        foreach ($pattern as $key => $value) {
            // Delimiter
            if (is_numeric($key)) {
                $markup .= $value;
            } else {
                $markup .= $data[$value];
            }
        }

        return trim($markup);
    }

    protected function renderDayInput($element)
    {
        return $this->wrap(
            $this->renderInput($element, 2),
            'Day',
            $element->getAttribute('id')
        );
    }

    protected function renderMonthInput($element)
    {
        return $this->wrap(
            $this->renderInput($element, 2),
            'Month',
            $element->getAttribute('id')
        );
    }

    protected function renderYearInput($element)
    {
        return $this->wrap(
            $this->renderInput($element, 4),
            'Year',
            $element->getAttribute('id')
        );
    }

    protected function wrap($content, $label, $id): string
    {
        $label = $this->getTranslator()->translate('date-' . $label);

        return sprintf($this->format, $id, $label, $content);
    }

    /**
     * @psalm-param 2|4 $maxLength
     */
    protected function renderInput($element, $maxLength)
    {
        $inputHelper = $this->getInputHelper();

        $element->setAttribute('maxlength', $maxLength);

        return $inputHelper->render($element);
    }

    protected function getInputHelper()
    {
        if ($this->inputHelper === null) {
            $this->inputHelper = $this->view->plugin('forminput');
        }

        return $this->inputHelper;
    }

    /**
     * Create a key => value options for minutes
     *
     * @param  string $pattern Pattern to use for minutes
     */
    #[\Override]
    protected function getMinutesOptions(string $pattern): array
    {
        $keyFormatter = new IntlDateFormatter(
            $this->getLocale(),
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT,
            null,
            IntlDateFormatter::GREGORIAN,
            'mm'
        );

        $valueFormatter = new IntlDateFormatter(
            $this->getLocale(),
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT,
            null,
            IntlDateFormatter::GREGORIAN,
            $pattern
        );

        $date           = new DateTime('1970-01-01 00:00:00');

        $displayEveryMinute = $this->getDisplayEveryMinute();
        if ($displayEveryMinute) {
            $modifier = '+1 minute';
            $from = 0;
            $to = 59;
        } else {
            $modifier = '+15 minute';
            $from = 1;
            $to = 4;
        }

        $result = [];

        for ($min = $from; $min <= $to; ++$min) {
            $key   = $keyFormatter->format($date);
            $value = $valueFormatter->format($date);
            $result[$key] = $value;

            $date->modify($modifier);
        }

        return $result;
    }

    /**
     * Get display every minute flag
     *
     * @return bool
     */
    protected function getDisplayEveryMinute()
    {
        return $this->displayEveryMinute;
    }

    /**
     * Set display every minute
     *
     * @param bool $displayEveryMinute display every minute flag
     *
     * @return void
     */
    protected function setDisplayEveryMinute($displayEveryMinute)
    {
        $this->displayEveryMinute = $displayEveryMinute;
    }
}
