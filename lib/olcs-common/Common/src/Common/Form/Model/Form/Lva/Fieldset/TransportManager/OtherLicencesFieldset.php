<?php

namespace Common\Form\Model\Form\Lva\Fieldset\TransportManager;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"class":"table__form","id":"hasOtherLicences"})
 * @Form\Name("otherLicencesFieldset")
 */
class OtherLicencesFieldset
{
    /**
     * @Form\Options({
     *     "label": "transport-manager.other-licence.form.radio.label",
     *     "hint" : "transport-manager.other-licence.form.radio.hint",
     *     "hint-class" : "",
     *     "value_options": {"Y":"Yes", "N":"No"},
     *     "label_attributes": {"class": "form-control form-control--radio form-control--inline"}
     * })
     * @Form\Validator("Common\Form\Elements\Validators\YesNoTableRequiredValidator",
     *     options={
     *          "table": "otherLicences",
     *          "message":"transport-manager-details.form.otherLicences.required"
     *     }
     * )
     * @Form\Type("Radio")
     */
    public $hasOtherLicences;

    /**
     * @Form\Name("otherLicences")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\Table")
     * @Form\Attributes({
     *     "id":"otherLicences",
     *     "class": "help__text help__text--removePadding"
     * })
     */
    public $otherLicences;
}
