<?php

namespace Olcs\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("text-search")
 * @Form\Attributes({"class":"","legend":"Filter By:"})
 */
class TextSearch
{
    /**
     * @Form\Attributes({"value":"", "class":"extra-long"})
     * @Form\Options({"label": "search.form.label", "hint": "search.form.hint"})
     * @Form\Type("Text")
     */
    public $search = null;
}
