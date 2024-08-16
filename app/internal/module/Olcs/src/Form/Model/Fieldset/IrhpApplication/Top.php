<?php

namespace Olcs\Form\Model\Fieldset\IrhpApplication;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class Top extends \Olcs\Form\Model\Fieldset\Base
{
    /**
     * @Form\Type("\Common\Form\Elements\Types\ReadonlyElement")
     * @Form\Options({
     *     "label": "Stock"
     * })
     */
    public $stockHtml = null;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     */
    public $stockText = null;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     */
    public $status = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     * @Form\Required(false)
     */
    public $id = null;

    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Attributes({"value":"<a id=""addOrRemoveCountriesButton"" class=""govuk-button govuk-button--secondary"" role=""button"" data-module=""govuk-button"">Add or remove countries</a>"})
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
     * @Form\Validator("Date", options={"format": "Y-m-d"})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $dateReceived = null;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     *
     */
    public $irhpPermitType;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     *
     */
    public $isApplicationPathEnabled;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     *
     */
    public $requiresPreAllocationCheck;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     *
     */
    public $licence;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     * @Form\Type("\Common\Form\Elements\Types\ReadonlyElement")
     * @Form\Options({
     *     "label": "Current total vehicle authorization"
     * })
     *
     */
    public $numVehiclesLabel;

    /**
     * @Form\Type("Laminas\Form\Element\Hidden")
     * @Form\Attributes({"id":"numVehicles"})
     *
     */
    public $numVehicles;
}
