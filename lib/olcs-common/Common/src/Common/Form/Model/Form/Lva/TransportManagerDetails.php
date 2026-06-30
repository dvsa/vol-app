<?php

namespace Common\Form\Model\Form\Lva;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"prefer_form_input_filter":true})
 * @Form\Name("lva-transport-manager-details")
 * @Form\Attributes({
 *     "method":"post",
 *     "autocomplete": "off",
 * })
 * @Form\Type("Common\Form\Form")
 */
class TransportManagerDetails
{
    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TransportManager\Details")
     * @Form\Flags({"priority": -10})
     */
    public $details;

    /**
     * @Form\Name("homeAddress")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     * @Form\Options({
     *     "label":"lva-tm-details-details-homeAddress",
     *     "label_attributes": {
     *         "aria-label": "Postcode search, enter home postcode",
     *         "id":"homeAddress"
     *     }
     * })
     * @Form\Flags({"priority": -20})
     */
    public $homeAddress;

    /**
     * @Form\Name("workAddress")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Address")
     * @Form\Options({
     *     "label":"lva-tm-details-details-workAddress"
     * })
     * @Form\Flags({"priority": -30})
     */
    public $workAddress;

    /**
     * @Form\Name("responsibilities")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TransportManager\Responsibilities")
     * @Form\Options({"label":"lva-tm-details-details-responsibilities"})
     * @Form\Flags({"priority": -40})
     */
    public $responsibilities;

    /**
     * @Form\Name("otherEmployments")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TransportManager\OtherEmployments")
     * @Form\Flags({"priority": -50})
     */
    public $otherEmployments;

    /**
     * @Form\Name("previousHistory")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TransportManager\PreviousHistory")
     * @Form\Flags({"priority": -60})
     */
    public $previousHistory;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\TmDetailsFormActions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\Flags({"priority": -70})
     */
    public $formActions;
}
