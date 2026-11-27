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

/**
 * Form Date Select
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FormDateSelect extends LaminasFormDateSelect
{
    use DateInputTrait;

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

        return $this->renderDatePattern($pattern, $data);
    }
}
