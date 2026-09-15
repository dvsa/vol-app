<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("filter")
 */
class ContinuationDetailFilter
{
    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "continuation-detail-filter-licenceNo"
     * })
     * @Form\Type("Text")
     */
    public $licenceNo;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "continuation-detail-filter-licenceStatus",
     *     "value_options": {
     *         "lsts_valid": "Valid",
     *         "lsts_suspended": "Suspended",
     *         "lsts_curtailed": "Curtailed",
     *         "lsts_revoked": "Revoked",
     *         "lsts_surrendered": "Surrendered",
     *         "lsts_terminated": "Terminated"
     *     }
     * })
     * @Form\Type("MultiCheckbox")
     */
    public $licenceStatus;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "continuation-detail-filter-method",
     *     "value_options": {
     *         "all": "All",
     *         "post": "Post",
     *         "email": "Email"
     *     }
     * })
     * @Form\Type("Select")
     */
    public $method;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "continuation-detail-filter-status",
     *     "value_options": {},
     *     "empty_option": "All",
     *     "category": "cont_d_status"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $status;
}
