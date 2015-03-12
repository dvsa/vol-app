<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("fields")
 * @Form\Options({
 *     "label": "Search"
 * })
 */
class BusRegFilterFields
{
    /**
     * @Form\Required(false)
     * @Form\Options({
     *     "label": "Upload type",
     *     "empty_option": "All",
     *     "disable_inarray_validator": false,
     *     "help-block": "Please select an upload type",
     *     "service_name": "Common\Service\Data\EbsrSubTypeListDataService",
     *     "category": "ebsr_sub_type"
     * })
     * @Form\Attributes({"id":"ebsr_submission_type","placeholder":""})
     * @Form\Type("DynamicSelect")
     */
    public $ebsrSubmissionType;

}
