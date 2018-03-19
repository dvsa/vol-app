<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;
//use Zend\Form\View\Helper\FormSelect;
use Common\Form\View\Helper\Readonly\FormSelect;


/**
 * @Form\Name("sectors-filter")
 * @Form\Attributes({"method":"get"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class sectorsFilter
{

    /**
     * @Form\Type("Select")
     * @Form\Options({
     *      "label": "Sectors",
     *      "value_options":{
     *          "0":"Select",
     *          "1":"Food products, beverages and tobacco; Products of agriculture, hunting, and forestry; fish and other fishing products",
     *          "2":"Coal and lignite; crude petroleum and natural gas",
     *          "3":"Metal ores and other mining and quarrying products; peat; uranium and thorium; Basic metals; fabricated metal products, except machinery and equipment",
     *          "4":"Textiles and textile products; leather and leather products",
     *          "5":"Wood and products of wood and cork (except furniture); articles of straw and plaiting materials; pulp, paper and paper products",
     *          "6":"Coke and refined petroleum products",
     *          "7":"Chemicals, chemical products, and man-made fibers; rubber and plastic products; nuclear fuel",
     *          "8":"Other non metallic mineral products",
     *          "9":"Transport equipment; Machinery and equipment n.e.c.; Equipment and material utilized in the transport of goods",
     *          "10":"Furniture; Other maufactured goods n.e.c.; Goods moved in course of households and office removals",
     *          "11":"Secondary raw materials; municipal wastes and other wastes",
     *          "12":"Mail, parcels; Grouped goods"
     *      }
     * })
     * @Form\Attributes({
     *      "id":"sectors",
     *      "value":"Select"
     * })
     */
    public $sectors = null;


    /**
     * @Form\Attributes({
     *     "id": "filter",
     *     "value": "Filter",
     *     "class": "action--primary large"
     * })
     * @Form\Type("Submit")
     */
    public $submit = null;
}
