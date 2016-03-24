<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("search-filter")
 * @Form\Attributes({
 *     "method":"post", 
 *     "class": "filters form__filter",
 *     "id": "filterContent"
 * })
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true, "bypass_auth": true})
 */
class SearchFilter
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $index = null;

    /**
     * @Form\Name("text")
     * @Form\Options({"label":"", "class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\TextSearch")
     */
    public $text = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $search = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({
     *     "label": "search.form.filter.update_button"
     * })
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $submit = null;

}
