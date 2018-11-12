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
     * @Form\Type("Hidden")
     */
    public $intensityWarning = 'no';

    /**
     * @Form\Name("InternationalJourney")
     * @Form\Attributes({
     *   "class" : "input--international-journey",
     *   "aria-labelledby" : "InternationalJourney",
     * })
     * @Form\Options({
     *      "fieldset-attributes": {"id": "international-journey"},
     *      "fieldset-data-group": "percentage-type",
     *      "label_attributes": {"class": "form-control form-control--radio"},
     *      "category": "inter_journey_percentage",
     * })
     * @Form\Type("DynamicRadio")
     */
    public $ecmtLicence = null;
}
