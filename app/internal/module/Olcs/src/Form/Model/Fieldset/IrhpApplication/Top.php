<?php

namespace Olcs\Form\Model\Fieldset\IrhpApplication;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class Top extends \Olcs\Form\Model\Fieldset\Base
{
    /**
     * @Form\Type("\Common\Form\Elements\Types\Readonly")
     * @Form\Options({
     *     "label": "Stock"
     * })
     */
    public $stockHtml = null;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     */
    public $stockText = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     * @Form\Required(false)
     */
    public $id = null;

    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Attributes({"value":"<a id=""addOrRemoveCountriesButton"" class=""action--secondary"">Add or remove countries</a>"})
     */
    public $addOrRemoveCountriesButton = null;

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
    public $isApplicationPathEnabled;

    /**
     * @Form\Type("Zend\Form\Element\Hidden")
     *
     */
    public $requiresPreAllocationCheck;

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
