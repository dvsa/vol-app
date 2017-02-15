<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("user-printer")
 */
class UserPrinter
{
    /**
     * @Form\Attributes({"id":"user","placeholder":""})
     * @Form\Options({
     *     "label": "User",
     *     "service_name": "Olcs\Service\Data\UserWithName",
     *     "empty_option": "Default"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $user = null;

    /**
     * @Form\Attributes({"id":"categoryUser","placeholder":""})
     * @Form\Options({
     *     "label": "Category",
     *     "service_name": "Olcs\Service\Data\Category",
     *     "empty_option": "Default setting"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $categoryUser = null;

    /**
     * @Form\Attributes({"id":"subCategoryUser","placeholder":""})
     * @Form\Options({
     *     "label": "Sub category",
     *     "service_name": "Olcs\Service\Data\SubCategory",
     *     "empty_option": "Default setting"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $subCategoryUser = null;

    /**
     * @Form\Attributes({"id":"printer","placeholder":""})
     * @Form\Options({
     *     "label": "Printer",
     *     "service_name": "Olcs\Service\Data\Printer"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     */
    public $printer = null;
}
