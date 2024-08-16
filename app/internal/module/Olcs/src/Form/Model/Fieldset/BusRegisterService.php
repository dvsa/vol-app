<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

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
     *      "id":"registerService[copiedToLaPte]",
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
     *      "id":"registerService[laShortNote]",
     *      "value":"N"
     * })
     */
    public $laShortNote;

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
     *     "label": "Variation reasons",
     * })
     * @Form\Attributes({
     *      "id":"variationReasons",
     * })
     */
    public $variationReasonsHtml = null;
}
