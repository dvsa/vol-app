<?php

namespace Common\Form\View\Helper;

trait DateInputTrait
{
    private $inputHelper;

    protected function renderDayInput($element)
    {
        return $this->renderDateInput($element, 'Day', 2);
    }

    protected function renderMonthInput($element)
    {
        return $this->renderDateInput($element, 'Month', 2);
    }

    protected function renderYearInput($element)
    {
        return $this->renderDateInput($element, 'Year', 4);
    }

    private function renderDateInput($element, string $label, int $width): string
    {
        $element->setAttribute('type', 'text');
        $element->setAttribute('maxlength', $width);
        $element->setAttribute('class', 'govuk-input govuk-date-input__input govuk-input--width-' . $width);
        $element->setAttribute('inputmode', 'numeric');

        return sprintf(
            '<div class="govuk-date-input__item"><div class="govuk-form-group">' .
            '<label class="govuk-label govuk-date-input__label" for="%s">%s</label>%s</div></div>',
            $this->getEscapeHtmlAttrHelper()($element->getAttribute('id')),
            $this->getEscapeHtmlHelper()($this->getTranslator()->translate('date-' . $label)),
            $this->getInputHelper()->render($element)
        );
    }

    private function renderDatePattern(array $pattern, array $data, bool $groupTime = false): string
    {
        $markup = '';
        $delimiter = '';
        $inDate = false;
        $inTime = false;

        foreach ($pattern as $key => $value) {
            if (is_numeric($key)) {
                $delimiter .= $value;
                continue;
            }

            $isDate = in_array($key, ['day', 'month', 'year'], true);
            if ($inDate && !$isDate) {
                $markup .= '</div>';
                $inDate = false;
            }

            if ($inTime && $isDate) {
                $markup .= $delimiter . '</div>';
                $delimiter = '';
                $inTime = false;
            }

            if (!$isDate && $groupTime && !$inTime) {
                $markup .= '<div class="date-time-input__time">';
                $inTime = true;
            }

            if ($inDate && $isDate && trim($delimiter) !== '') {
                $markup .= '<span class="govuk-body">' . $delimiter . '</span>';
            } else {
                $markup .= $delimiter;
            }
            $delimiter = '';

            if ($isDate && !$inDate) {
                $markup .= '<div class="govuk-date-input">';
                $inDate = true;
            }

            $markup .= $data[$value];
        }

        return $markup . $delimiter . (($inDate || $inTime) ? '</div>' : '');
    }

    protected function getInputHelper()
    {
        if ($this->inputHelper === null) {
            $this->inputHelper = $this->view->plugin('forminput');
        }

        return $this->inputHelper;
    }
}
