<?php

declare(strict_types=1);

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-vehicles-declarations-written")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 */
class VehiclesDeclarationsWritten
{
    /**
     * @Form\Attributes({
     *     "value": "markup-psv-written-evidence-form"
     * })
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $writtenEvidenceText;

    /**
     * @Form\Attributes({"id":"","class":"long"})
     * @Form\Options({
     *     "legend-attributes": {"class": "form-element__label"},
     *     "label": "application_psv_written_evidence.title"
     * })
     * @Form\Type("Textarea")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={
     *     "min": 50, "max": 4000
     *})
     */
    public $psvSmallVhlNotes;

    /**
     * @Form\Attributes({"class":"tiny", "id":""})
     * @Form\Options({
     *     "label": "application_written-evidence.eightSeats.label",
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Between", options={
     *     "min":1, "max":99999
     *})
     */
    public $psvTotalVehicleSmall;

    /**
     * @Form\Attributes({"class":"tiny", "id":""})
     * @Form\Options({
     *     "label": "application_written-evidence.nineSeats.label",
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Between", options={
     *     "min":1, "max":99999
     *})
     */
    public $psvTotalVehicleLarge;

    /**
     * @Form\Name("version")
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     */
    public $formActions;
}
