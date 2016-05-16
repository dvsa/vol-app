<?php

/**
 * @todo remove after task allocation rules will be tested (OLCS-6844 & OLCS-12638)
 */
namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @Form\Options({"label":"Create task"})
 */
class CreateTaskTempDetails
{
    /**
     * @Form\Attributes({"id":"category","placeholder":""})
     * @Form\Options({
     *     "label": "scanning.data.category",
     *     "service_name": "Olcs\Service\Data\Category",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $category = null;

    /**
     * @Form\Attributes({"id":"subCategory","placeholder":""})
     * @Form\Options({
     *     "label": "scanning.data.sub_category",
     *     "service_name": "Olcs\Service\Data\SubCategory",
     *     "context": {
     *       "isScan": true
     *     }
     * })
     * @Form\Type("DynamicSelect")
     */
    public $subCategory = null;

    /**
     * @Form\Required(false)
     * @Form\Attributes({"id":"entity_identifier","placeholder":""})
     * @Form\Options({
     *     "label": "Licence ID"
     * })
     * @Form\Type("Text")
     */
    public $entityIdentifier = null;
}
