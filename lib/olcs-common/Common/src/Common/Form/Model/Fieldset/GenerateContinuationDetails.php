<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Type("Laminas\Form\Fieldset")
 */
class GenerateContinuationDetails
{
    /**
     * @Form\Attributes({"id":"generate-continuation-type","placeholder":"","value":"con_typ_operator"})
     * @Form\Options({
     *     "label": "Type",
     *     "value_options": {
     *         "operator": "Operator licences",
     *         "irfo": "IRFO PSV Authorisations"
     *     }
     * })
     * @Form\Type("Select")
     */
    public $type;

    /**
     * @Form\Attributes({"id":"generate-continuation-date","placeholder":""})
     * @Form\Options({
     *     "label": "Date",
     *     "min_year_delta": "-30",
     *     "max_year_delta": "+10",
     *     "default_date": "now"
     * })
     * @Form\Type("MonthSelect")
     */
    public $date;

    /**
     * @Form\Attributes({"id":"generate-continuation-trafficArea","placeholder":""})
     * @Form\Options({
     *     "label": "Traffic area",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\TrafficArea"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $trafficArea;
}
