<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("BusRegBrowse")
 * @Form\Attributes({"method":"post"})
 * @Form\Options({"prefer_form_input_filter": true})
 */
class BusRegBrowse
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
}
