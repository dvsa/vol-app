<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("bus-service-number-and-type")
 */
class BusRegisterService extends Base
{
    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Application copied to LA/PTE",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"conditions[copiedToLaPte]",
     *      "value":"N"
     * })
     */
    public $copiedToLaPte;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "LA/PTE support short notice",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"registerService[isShortNotice]",
     *      "value":"N"
     * })
     */
    public $isShortNotice;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Application signed",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"registerService[applicationSigned]",
     *      "value":"N"
     * })
     */
    public $applicationSigned;

    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Options({
     *     "label": "Variation details",
     * })
     * @Form\Attributes({
     *      "id":"variationDetails",
     *      "value":"Lorem ipsum"
     * })
     */
    public $variationDetails = null;

    /**
     * @Form\Attributes({
     *      "id":"correspondenceAddress",
     *      "placeholder":"",
     *      "class":"chosen-select-medium"
     * })
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Correspondence address",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\AddressListDataService",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $correspondenceAddress = null;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Operator notified LA/PTE 14 days prior",
     *      "value_options":{
     *          "N":"No",
     *          "Y":"Yes"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"registerService[opNotifiedLaPte]",
     *      "value":"N"
     * })
     */
    public $opNotifiedLaPte;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "Date completed",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Type("DateSelect")
     * @Form\Required(false)
     * @Form\Validator({"name":"Date", "options":{"format":"Y-m-d"}})
     * @Form\Filter({"name": "DateSelectNullifier"})
     */
    public $completedDate = null;
}
