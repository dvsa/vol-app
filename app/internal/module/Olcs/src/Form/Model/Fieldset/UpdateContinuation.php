<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("update-continuation")
 */
class UpdateContinuation
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Checklist received",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
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
     * @Form\Validator("Digits")
     */
    public $totalVehicleAuthorisation = null;

    /**
     * @Form\Options({
     *     "label": "Number of discs",
     * })
     * @Form\Type("Text")
     * @Form\Validator("Digits")
     */
    public $numberOfDiscs = null;

    /**
     * @Form\Options({
     *     "label": "Number of community licences",
     * })
     * @Form\Type("Text")
     * @Form\Validator("Digits")
     */
    public $numberOfCommunityLicences = null;

    /**
     * @Form\Name("message")
     * @Form\Type("Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $message;
}
