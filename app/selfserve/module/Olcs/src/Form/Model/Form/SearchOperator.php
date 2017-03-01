<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("search-operator")
 * @Form\Attributes({"method":"post", "action":"", "role":"search"})
 */
class SearchOperator
{
    /**
     * @Form\Name("searchBy")
     * @Form\Options({
     *     "label": "search.operator.field.searchBy.label",
     *     "label_attributes": {"class": "form-control form-control--radio"},
     *     "value_options":{
     *          "address":"search.operator.field.searchBy.address.label",
     *          "business":"search.operator.field.searchBy.business.label",
     *          "licence":"search.operator.field.searchBy.licence.label",
     *          "person":"search.operator.field.searchBy.person.label"
     *      }
     * })
     * @Form\Required(true)
     * @Form\Attributes({"id":"searchBy", "placeholder":"", "value":"address"})
     * @Form\Type("Radio")
     */
    public $searchBy;

    /**
     * @Form\Attributes({"class": "long", "placeholder": "", "label":"some"})
     * @Form\Type("Text")
     * @Form\Validator({"name": "NotEmpty"})
     * @Form\Options({
     *     "label": "search.operator.field.search.label",
     *     "error-message": "simpleSearch_search-error"
     * })
     */
    protected $search;

    /**
     * @Form\Attributes({
     *    "class": "action--primary large",
     *    "value": "lookup-basic-search-submit"
     * })
     * @Form\Type("Submit")
     */
    protected $submit;

    /**
     * @Form\Type("Hidden")
     */
    protected $index;
}
