<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("update-continuation")
 * @Form\Options({"label":"Update continuation"})
 */
class UpdateContinuation
{
    /**
     * @Form\Options({
     *      "label":"Received",
     *      "checked_value":"Y",
     *      "unchecked_value":"N"
     * })
     * @Form\Type("OlcsCheckbox")
     */
    public $received = null;

    /**
     * @Form\Options({
     *     "label": "Checklist status",
     *     "category": "cont_d_status",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $checklistStatus = null;

    /**
     * @Form\Options({
     *     "label": "Total vehicle authorisation",
     * })
     * @Form\Type("Text")
     * @Form\Validator({"name":"Digits"})
     */
    public $totalVehicleAuthorisation = null;

    /**
     * @Form\Options({
     *     "label": "Number of discs",
     * })
     * @Form\Type("Text")
     * @Form\Validator({"name":"Digits"})
     */
    public $numberOfDiscs = null;

    /**
     * @Form\Options({
     *     "label": "Number of community licences",
     * })
     * @Form\Type("Text")
     * @Form\Validator({"name":"Digits"})
     */
    public $numberOfCommunityLicences = null;

    /**
     * @Form\Name("message")
     * @Form\Type("Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $message;
}
