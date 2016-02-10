<?php

namespace Admin\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("team-printer")
 */
class TeamPrinter
{
    /**
     * @Form\Attributes({"id":"categoryTeam","placeholder":""})
     * @Form\Options({
     *     "label": "Category",
     *     "service_name": "Olcs\Service\Data\DocumentCategory"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     */
    public $categoryTeam = null;

    /**
     * @Form\Attributes({"id":"subCategoryTeam","placeholder":""})
     * @Form\Options({
     *     "label": "Sub category",
     *     "service_name": "Olcs\Service\Data\DocumentSubCategory"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(true)
     */
    public $subCategoryTeam = null;

    /**
     * @Form\Attributes({"id":"printer","placeholder":""})
     * @Form\Options({
     *     "label": "Printer",
     *     "service_name": "Olcs\Service\Data\Printer"
     * })
     * @Form\Type("DynamicSelect")
     * @Form\Required(false)
     */
    public $printer = null;
}
