<?php

namespace Admin\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("editableTranslation")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"label": "Edit translations"})
 */
class EditableTranslationSearch
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\EditableTranslationSearch")
     */
    public $fields = null;

    /**
     * @Form\Attributes({
     *     "type":"submit",
     *     "class":"action--primary large",
     *     "aria-label": "Search"
     * })
     * @Form\Options({"label": "Search"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $search = null;
}
