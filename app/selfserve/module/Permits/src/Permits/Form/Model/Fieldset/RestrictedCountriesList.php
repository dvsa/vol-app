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
     * @Form\Options({
     *     "label": "markup-ecmt-restricted-countries-list-label",
     *     "label_attributes":{
     *          "class" : "form-control form-control--checkbox"
     *     },
     *     "service_name": "Common\Service\Data\Country",
     *     "category": "",
     *     "disable_inarray_validator" : true,
     * })
     * @Form\Attributes({
     *     "class" : "input--trips",
     *     "id" : "RestrictedCountriesList",
     *     "allowWrap":true,
     *     "data-container-class": "form-control__container",
     * })
     * @Form\Type("DynamicMultiCheckbox")
     */
    public $restrictedCountriesList;

}
