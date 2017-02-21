<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("note-filter")
 * @Form\Attributes({"method":"get", "class":"filters form__filter"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class NoteFilter
{
    /**
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "internal-licence-processing-notes.data.notetype",
     *     "disable_inarray_validator": false,
     *     "category": "note_type",
     *     "empty_option": "All",
     *     "exclude":{"note_t_org"}
     * })
     * @Form\Type("DynamicSelect")
     */
    public $noteType;

    /**
     * @Form\Attributes({"value":"priority"})
     * @Form\Type("Hidden")
     */
    public $sort;

    /**
     * @Form\Attributes({"value":"DESC"})
     * @Form\Type("Hidden")
     */
    public $order;

    /**
     * @Form\Attributes({"value":"10"})
     * @Form\Type("Hidden")
     */
    public $limit;

    /**
     * @Form\Attributes({"value":"1"})
     * @Form\Type("Hidden")
     */
    public $page;
}
