<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("EcmtLicence")
 */
class EcmtLicence
{
    /**
     * @Form\Name("EcmtLicence")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--trips",
     *    "id" : "EcmtLicence",
     * })
     * @Form\Options({
     *      "label": "",
     *      "fieldset-attributes": {"id": "ecmt-licence"},
     *      "fieldset-data-group": "licence-type",
     *      "label_attributes": {"class": "form-control form-control--radio"}
     * })
     * @Form\Type("Radio")
     */
    public $ecmtLicence = null;

    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({
     *     "class":"action--primary large top",
     *     "id":"submitbutton",
     *     "value":"Save and continue",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $submit = null;

    /**
     * @Form\Name("Cancel")
     * @Form\Attributes({
     *     "class":"action--primary large return-overview",
     *     "id":"save-return-button",
     *     "value":"Cancel",
     * })
     * @Form\Type("Zend\Form\Element\Submit")
     */
    public $save = null;
}
