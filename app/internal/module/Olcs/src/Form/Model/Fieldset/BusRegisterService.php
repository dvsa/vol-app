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
     *      "id":"applicationCopiedToLaPte",
     * })
     */
    public $applicationCopiedToLaPte;

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
     *      "id":"shortNoticeSupported",
     * })
     */
    public $shortNoticeSupported;

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
     *      "id":"applicationSigned",
     * })
     */
    public $applicationSigned;

    /**
     * @Form\Type("Common\Form\Elements\Types\Html")
     * @Form\Options({
     *     "label": "Variation details",
     * })
     */
    public $variationDetails = null;
}
