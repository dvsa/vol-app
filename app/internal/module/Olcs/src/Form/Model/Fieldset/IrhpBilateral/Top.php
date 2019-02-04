<?php

namespace Olcs\Form\Model\Fieldset\IrhpBilateral;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class Top extends \Olcs\Form\Model\Fieldset\Base
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     * @Form\Required(false)
     */
    public $id = null;

    /**
     * @Form\Options({
     *     "label": "<h4>Annual Bilateral Permit Application</h4>",
     *     "label_options": {
     *         "disable_html_escape": "true"
     *     }
     * })
     *
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $title = null;

    /**
     * @Form\Attributes({"id":"dateReceived"})
     * @Form\Options({
     *     "label": "Date received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Required(false)
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "\Common\Validator\Date"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $dateReceived = null;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     *
     */
    public $irhpPermitType;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     *
     */
    public $licence;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     * @Form\Type("\Common\Form\Elements\Types\Readonly")
     * @Form\Options({
     *     "label": "Current total vehicle authorization"
     * })
     *
     */
    public $numVehiclesLabel;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     * @Form\Attributes({"id":"numVehicles"})
     *
     */
    public $numVehicles;
}
