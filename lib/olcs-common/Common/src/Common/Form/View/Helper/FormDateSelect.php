<?php

/**
 * Form Date Select
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\View\Helper;

use Laminas\Form\View\Helper\FormDateSelect as LaminasFormDateSelect;
use Laminas\Form\ElementInterface;
use Laminas\Form\Element\DateSelect as DateSelectElement;
use Laminas\Form\Exception;
use Laminas\Form\View\Helper\FormMonthSelect as FormMonthSelectHelper;

/**
 * Form Date Select
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FormDateSelect extends LaminasFormDateSelect
{
    private $inputHelper;

    private $format = '<div class="field inline-text"><label for="%s">%s</label>%s</div>';

    /**
     * Render a date element that is composed of three selects
     *
     * @throws \Laminas\Form\Exception\InvalidArgumentException
     * @throws \Laminas\Form\Exception\DomainException
     */
    #[\Override]
    public function render(ElementInterface $element): string
    {
        if (!$element instanceof DateSelectElement) {
            throw new Exception\InvalidArgumentException(
                sprintf(
                    '%s requires that the element is of type Laminas\Form\Element\DateSelect',
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

        $pattern      = $this->parsePattern($element->shouldRenderDelimiters());

        $dayElement   = $element->getDayElement();
        $monthElement = $element->getMonthElement();
        $yearElement  = $element->getYearElement();

        // set ids and patterns on each input field
        $dayElement->setAttributes(['id' => $element->getAttribute('id') . '_day', 'pattern' => '\d*']);
        $monthElement->setAttributes(['id' => $element->getAttribute('id') . '_month', 'pattern' => '\d*']);
        $yearElement->setAttributes(['id' => $element->getAttribute('id') . '_year', 'pattern' => '\d*']);

        $data = [];
        $data[$pattern['day']]   = $this->renderDayInput($dayElement);
        $data[$pattern['month']] = $this->renderMonthInput($monthElement);
        $data[$pattern['year']]  = $this->renderYearInput($yearElement);

        $markup = '';
        foreach ($pattern as $key => $value) {
            // Delimiter
            if (is_numeric($key)) {
                $markup .= $value;
            } else {
                $markup .= $data[$value];
            }
        }

        return $markup;
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
        $element->setAttribute('class', 'govuk-input govuk-date-input__input govuk-input--width-' . $maxLength);

        return $inputHelper->render($element);
    }

    protected function getInputHelper()
    {
        if ($this->inputHelper === null) {
            $this->inputHelper = $this->view->plugin('forminput');
        }

        return $this->inputHelper;
    }
}
