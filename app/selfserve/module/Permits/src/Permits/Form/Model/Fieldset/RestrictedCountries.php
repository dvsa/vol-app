<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("RestrictedCountries")
 */
class RestrictedCountries
{

    /**
     * @Form\Name("restrictedCountries")
     * @Form\Required(true)
     * @Form\Attributes({
     *   "class" : "input--trips",
     * })
     * @Form\Options({
     *     "label": "",
     *     "label_attributes":{
     *          "class" : "form-control form-control--radio restrictedRadio"
     *     },
     *     "value_options":{
     *          "1" : "Yes",
     *          "0" : "No"
     *     }
     * })
     * @Form\Type("Radio")
     */
    public $restrictedCountries = null;


    /**
     * @Form\Name("restrictedCountriesList")
     * @Form\Required(true)
     * @Form\Options({
     *     "label": "",
     *     "label_attributes":{
     *          "class" : "form-control form-control--checkbox"
     *     }
     * })
     * @Form\Type("MultiCheckBox")
     */
    public $restrictedCountriesList = null;

}

?>
