<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("InternationalJourney")
 */
class InternationalJourney
{
    /**
     * @Form\Name("InternationalJourney")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--international-journey",
     *    "id" : "InternationalJourney",
     * })
     * @Form\Options({
     *      "label": "",
     *      "fieldset-attributes": {"id": "international-journey"},
     *      "fieldset-data-group": "percentage-type",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "value_options": {
     *          "less.than.60%",
     *          "from.60%.to.90%",
     *          "more.than.90%",
     *      },
     *      "error-message": "error.messages.international-journey"
     * })
     * @Form\Type("Radio")
     */
    public $ecmtLicence = null;

}
