<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
use Common\Form\Model\Form\Traits\IdTrait;
use Common\Form\Model\Form\Traits\VersionTrait;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class":""})
 * @Form\Name("printer-details")
 */
class PrinterDetails
{
    use VersionTrait,
        IdTrait;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Printer"})
     * @Form\Type("Text")
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":45}})
     */
    public $printerName = null;

    /**
     * @Form\Attributes({"placeholder":"","class":"medium"})
     * @Form\Options({"label":"Description"})
     * @Form\Type("Text")
     * @Form\Required(false)
     * @Form\Filter({"name":"Zend\Filter\StringTrim"})
     * @Form\Validator({"name":"Zend\Validator\StringLength","options":{"max":255}})
     */
    public $description = null;
}
