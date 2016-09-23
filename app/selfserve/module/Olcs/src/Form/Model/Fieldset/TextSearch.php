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
     * @Form\Name("search")
     * @Form\Attributes({"value":"", "class":"extra-long"})
     * @Form\Options({"label": "search.form.label"})
     * @Form\Type("Text")
     */
    public $search = null;
}
