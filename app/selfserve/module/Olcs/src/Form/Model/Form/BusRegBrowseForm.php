<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("bus-reg-browse-form")
 * @Form\Attributes({"method":"post", "action":""})
 * @Form\Options({"prefer_form_input_filter": true})
 */
class BusRegBrowseForm
{
    /**
     * @Form\Options({
     *     "label": "selfserve.search.busreg.browse.form.trafficAreas.label",
     *     "disable_inarray_validator": false,
     *     "service_name": "Common\Service\Data\TrafficArea",
     *     "exclude": {"N"},
     *     "use_groups": "false"
     * })
     * @Form\Attributes({"id":"trafficAreas","placeholder":""})
     * @Form\Type("DynamicMultiCheckbox")
     */
    public $trafficAreas;

    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "selfserve.search.busreg.browse.form.status.label",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "category": "bus_reg_browse_status"
     * })
     * @Form\Attributes({"id":"status", "placeholder":"", "class":"long"})
     * @Form\Type("DynamicSelect")
     */
    public $status;

    /**
     * @Form\Options({
     *     "label": "selfserve.search.busreg.browse.form.acceptedDate.label",
     *     "render_delimiters": false
     * })
     * @Form\Attributes({"id":"acceptedDate", "placeholder":""})
     * @Form\Type("DateSelect")
     * @Form\Filter({"name": "DateSelectNullifier"})
     * @Form\Validator({"name": "Date", "options": {"format": "Y-m-d"}})
     * @Form\Validator({"name": "\Common\Form\Elements\Validators\DateNotInFuture"})
     */
    public $acceptedDate;

    /**
     * @Form\Attributes({
     *    "class": "action--primary large", 
     *    "value": "selfserve.search.busreg.browse.form.submit.label"
     * })
     * @Form\Type("Submit")
     */
    protected $submit;
}
