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
     *          "Less than 60%",
     *          "From 60% to 90%",
     *          "More than 90%",
     *      },
     * })
     * @Form\Type("Radio")
     */
    public $ecmtLicence = null;

}
