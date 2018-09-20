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
     *      "fieldset-attributes": {"id": "ecmt-licence"},
     *      "fieldset-data-group": "licence-type",
     *      "label_attributes": {"class": "form-control form-control--radio restricted-licence-input"},
     *      "service_name": "Common\Service\Data\EcmtLicence",
     *      "disable_inarray_validator" : true,
     * })
     * @Form\Validator({
     *      "name": "Zend\Validator\NotEmpty",
     *      "options": {
     *          "message": {
     *              "isEmpty": "error.messages.ecmt-licence"
     *          }
     *     }
     * })
     * @Form\Type("DynamicRadioHtml")
     */







    public $ecmtLicence = null;
}
