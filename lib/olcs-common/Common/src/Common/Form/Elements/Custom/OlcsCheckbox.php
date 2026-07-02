<?php

namespace Common\Form\Elements\Custom;

use Laminas\Form\Element as LaminasElement;
use Laminas\Validator as LaminasValidator;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Form\LabelAwareInterface;

/**
 * OlcsCheckbox element
 *
 * @author Someone <someone@valtech.co.uk>
 */
class OlcsCheckbox extends LaminasElement\Checkbox implements LabelAwareInterface
{
    public function __construct($name = null, $options = [])
    {
        $labelPosition = $this->getLabelOption('label_position');
        if (empty($labelPosition)) {
            $this->setLabelOption('label_position', \Laminas\Form\View\Helper\FormRow::LABEL_APPEND);
        }

        $alwaysWrap = $this->getLabelOption('always_wrap');
        if (empty($alwaysWrap)) {
            $this->setLabelOption('always_wrap', true);
        }

        parent::__construct($name, $options);
    }
}
