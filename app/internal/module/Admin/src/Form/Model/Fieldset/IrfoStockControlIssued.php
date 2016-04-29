<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * IRFO Stock Control Issued form.
 */
class IrfoStockControlIssued
{
    /**
     * @Form\Attributes({"id":"","placeholder":"","class":"small"})
     * @Form\Options({"label": "IRFO GV Permit No"})
     * @Form\Type("Text")
     * @Form\Validator({"name":"Digits"})
     * @Form\Validator({
     *     "name":"GreaterThan",
     *     "options": {
     *         "min":"0",
     *     }
     * })
     */
    public $irfoGvPermitId;
}
