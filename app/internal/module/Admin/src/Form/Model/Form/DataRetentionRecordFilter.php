<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("data-retention-record-filter")
 * @Form\Attributes({
 *     "method": "GET",
 *     "class": "filters form__filter",
 * })
 * @Form\Type("Common\Form\Form")
 * @Form\Options({
 *     "prefer_form_input_filter": true,
 * })
 */
class DataRetentionRecordFilter
{
    /**
     * @Form\Attributes({"id":"nextReview","placeholder":""})
     * @Form\Options({
     *     "label": "Next review",
     *     "value_options": {
     *          "":"All",
     *          "pending":"Pending review",
     *          "deferred":"Deferred review"
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $nextReview = null;

    /**
     * @Form\Attributes({"id":"markedForDeletion","placeholder":""})
     * @Form\Options({
     *     "label": "Marked for deletion",
     *     "value_options": {
     *          "":"All",
     *          "N":"No",
     *          "Y":"Yes"
     *     },
     *     "disable_inarray_validator": false
     * })
     * @Form\Type("\Zend\Form\Element\Select")
     */
    public $markedForDeletion = null;

    /**
     * @Form\Attributes({"id":"assignedTo","placeholder":"",
     *     "class":"chosen-select-large"})
     * @Form\Options({
     *     "label": "Assigned to",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\UserListInternal",
     *     "use_groups": "false"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $assignedTo = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary"})
     * @Form\Options({
     *     "label": "filter-button"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $filter = null;
}
