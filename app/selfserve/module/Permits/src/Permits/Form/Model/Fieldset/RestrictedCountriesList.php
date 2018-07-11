<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("RestrictedCountriesList")
 * @Form\Attributes({
 *     "class" : "restricted-countries-list guidance"
 * })
 */
class RestrictedCountriesList
{
    /**
     * @Form\Name("restrictedCountriesList")
     * @Form\Required(false)
     * @Form\Attributes({
     *      "allowWrap":true,
     *      "data-container-class": "form-control__container",
     *     })
     * @Form\Options({
     *     "label": "markup-ecmt-restricted-countries-list-label",
     *     "label_attributes":{
     *          "class" : "form-control form-control--checkbox"
     *     },
     *     "disable_inarray_validator" : true,
     * })
     * @Form\Type("MultiCheckBox")
     */
    public $restrictedCountriesList = null;

}
