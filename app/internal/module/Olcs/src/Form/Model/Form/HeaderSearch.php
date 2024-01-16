<?php

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("search")
 * @Form\Attributes({"method":"GET", "action": "/search"})
 * @Form\Hydrator("Laminas\Hydrator\ArraySerializable")
 */
class HeaderSearch
{
    /**
     * @Form\Attributes({"class": "search__input", "placeholder": "Search"})
     * @Form\Type("Text")
     * @Form\Validator("NotEmpty")
     */
    protected $search;

    /**
     * @Form\Attributes({
     *      "id": "search-select"
     * })
     * @Form\Type("DynamicSelect")
     */
    protected $index;

    /**
     * @Form\Attributes({"class": "search__button"})
     * @Form\Type("Submit")
     */
    protected $submit;
}
