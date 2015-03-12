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
     * @Form\Options({
     *     "label": "EBSR file",
     *      "value_options":{
     *          "all":"All"
     *          "N":"No",
     *          "Y":"Yes"
     *      }
     * })
     * @Form\Attributes({"id":"file_type", "value":"all"})
     * @Form\Type("Select")
     */
    public $fileType;

    /**
     * @Form\Options({
     *     "label": "Status",
     *     "empty_option": "Please Select",
     *     "disable_inarray_validator": false,
     *     "category": "lic_status"
     * })
     * @Form\Attributes({"id":"","placeholder":"", "required":false})
     * @Form\Type("DynamicSelect")
     */
    public $status = null;
}
