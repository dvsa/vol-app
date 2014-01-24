<?php
namespace OlcsSelfserve\View\Helper;

use DateTime;
use OlcsSelfserve\Form\Element\DateSelect as DateSelectElement;
use Zend\Form\ElementInterface;
use Zend\Form\Exception;
use Zend\Form\View\Helper\FormText as FormText;
use Zend\Form\View\Helper\FormDateSelect as ZendFormDateSelect;

class FormDateSelect extends ZendFormDateSelect
{
    /**
     * FormText helper
     *
     * @var FormText
     */
    protected $textHelper;

    /**
     * Removed international time support
     */
    public function __construct()
    {
        $this->dateType = 'd F Y';
    }

    /**
     * Removed international time support
     */
    public function __invoke(ElementInterface $element = null, $dateType = null, $locale = null)
    {
        return parent::__invoke($element, $dateType, $locale);
    }

    /**
     * Removed international time support
     */
    public function setDateType($dateType)
    {
        $this->dateType = $dateType;
        return $this;
    }

    /**
     * Removed international time support, pattern set in code instead
     */
    public function getPattern()
    {
        if (null === $this->pattern) {
            $this->pattern = 'd F Y';
        }

        return $this->pattern;
    }

    protected function parsePattern($renderDelimiters = true)
    {
        $pattern    = $this->getPattern();
        $pregResult = preg_split("/([ -,.\/]*(?:'[a-zA-Z]+')*[ -,.\/]+)/", $pattern, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $result = array();
        foreach ($pregResult as $value) {
            if (stripos($value, "'") === false && stripos($value, 'd') !== false) {
                $result['day'] = $value;
            } elseif (stripos($value, "'") === false && (stripos($value, 'm') !== false || strpos($value, 'F') !== false)) {
                $result['month'] = $value;
            } elseif (stripos($value, "'") === false && stripos($value, 'y') !== false) {
                $result['year'] = $value;
            } elseif ($renderDelimiters) {
                $result[] = str_replace("'", '', $value);
            }
        }

        return $result;
    }

    protected function getDaysOptions($pattern)
    {
        $date = new DateTime('1970-01-01');

        $result = array();
        for ($day = 1; $day <= 31; $day++) {
            $key   = $date->format('d');
            $value = $date->format($pattern);
            $result[$key] = $value;

            $date->modify('+1 day');
        }

        return $result;
    }

    protected function getMonthsOptions($pattern)
    {
        $date = new DateTime('1970-01-01');

        $result = array();
        for ($month = 1; $month <= 12; $month++) {
            $key   = $date->format('m');
            $value = $date->format($pattern);
            $result[$key] = $value;

            $date->modify('+1 month');
        }

        return $result;
    }

    /**
     * Render a date element that is composed of two selects and one text box
     *
     * @param  ElementInterface $element
     * @throws \Zend\Form\Exception\InvalidArgumentException
     * @throws \Zend\Form\Exception\DomainException
     * @return string
     */
    public function render(ElementInterface $element)
    {
        if (!$element instanceof DateSelectElement) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s requires that the element is of type Zend\Form\Element\DateSelect',
                __METHOD__
            ));
        }

        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                '%s requires that the element has an assigned name; none discovered',
                __METHOD__
            ));
        }

        $selectHelper = $this->getSelectElementHelper();
        $textHelper   = $this->getTextElementHelper();
        $pattern      = $this->parsePattern($element->shouldRenderDelimiters());

        $daysOptions   = $this->getDaysOptions($pattern['day']);
        $monthsOptions = $this->getMonthsOptions($pattern['month']);

        $dayElement   = $element->getDayElement()->setValueOptions($daysOptions);
        $monthElement = $element->getMonthElement()->setValueOptions($monthsOptions);
        $yearElement  = $element->getYearElement();

        if ($element->shouldCreateEmptyOption()) {
            $resourceHelper = $this->view->plugin('resourceHelper');
            $dayElement->setEmptyOption($resourceHelper('day'));
            $monthElement->setEmptyOption($resourceHelper('month'));
        }

        $data = array();
        $data[$pattern['day']]   = '<span class="date">' . $selectHelper->render($dayElement) . '</span>';
        $data[$pattern['month']] = '<span class="month">' . $selectHelper->render($monthElement) . '</span>';
        $data[$pattern['year']]  = '<span class="year">' . $textHelper->render($yearElement) . '</span>';

        $markup = '';
        foreach ($pattern as $key => $value) {
            // Delimiter
            if (is_numeric($key)) {
                $markup .= $value;
            } else {
                $markup .= $data[$value];
            }
        }

        return '<span class="olcs-date-select">' . $markup . '</span>';
    }

    /**
     * Retrieve the FormSelect helper
     *
     * @return FormSelect
     */
    protected function getTextElementHelper()
    {
        if ($this->textHelper) {
            return $this->textHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->textHelper = $this->view->plugin('formtext');
        }

        return $this->textHelper;
    }
}
