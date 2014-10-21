<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("search")
 * @Form\Attributes({"method":"post", "action": "/search"})
 * @Form\Hydrator("Zend\Stdlib\Hydrator\ArraySerializable")
 */
class HeaderSearch
{
    /**
     * @Form\Attributes({"class": "search__input", "placeholder": "Search"})
     * @Form\Type("Text")
     */
    protected $search;

    /**
     * @Form\Attributes({"class": "search__select", "id": "search-select", "style": "position:absolute; top:3px; right:40px"})
     * @Form\Type("Select")
     * @Form\Options({
     *      "value_options": {
     *          "licence": "Licence",
     *          "application": "Application",
     *          "case": "Cases"
     *      }
     * })
     */
    protected $index;

    /**
     * @Form\Attributes({"class": "search__button"})
     * @Form\Type("Submit")
     */
    protected $submit;
}
