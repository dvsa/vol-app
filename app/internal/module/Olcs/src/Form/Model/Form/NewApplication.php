<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("new_application")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class NewApplication
{
    /**
     * @Form\Attributes({"id":"receivedDate"})
     * @Form\Options({
     *     "label": "Application received",
     *     "create_empty_option": true,
     *     "render_delimiters": false
     * })
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     * @Form\Type("DateSelect")
     */
    public $receivedDate = null;

    /**
     * @Form\Attributes({"id":"trafficArea","placeholder":""})
     * @Form\Options({
     *     "label": "Traffic area",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\TrafficArea",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $trafficArea = null;

    /**
     * @Form\Type("Radio")
     * @Form\Options({
     *      "label": "Method of application",
     *      "value_options":{
     *          "applied_via_post":"Post",
     *          "applied_via_phone":"Phone"
     *      },
     *      "fieldset-attributes" : {
     *          "class":"inline"
     *      }
     * })
     */
    protected $appliedVia = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CreateButtons")
     */
    public $formActions = null;
}
