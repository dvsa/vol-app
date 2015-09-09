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
     * @Form\Attributes({"value":""})
     * @Form\Options({"label":"Keywords", "class":"extra-long"})
     * @Form\Type("Text")
     */
    public $search = null;
}
