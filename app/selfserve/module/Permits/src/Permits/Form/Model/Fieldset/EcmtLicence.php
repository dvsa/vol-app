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
     *      "label": "Choose one licence below",
     *      "fieldset-attributes": {"id": "ecmt-licence"},
     *      "fieldset-data-group": "licence-type",
     *      "label_attributes": {"class": "form-control form-control--radio"}
     * })
     * @Form\Type("Radio")
     */
    public $ecmtLicence = null;

}
