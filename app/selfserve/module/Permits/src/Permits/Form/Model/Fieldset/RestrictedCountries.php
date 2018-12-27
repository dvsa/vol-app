<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("RestrictedCountries")
 * @Form\Type("\Common\Form\Elements\Types\RadioVertical")
 * @Form\Options({
 *     "radio-element": "restrictedCountries"
 * })
 */
class RestrictedCountries
{
    /**
     * @Form\Required(true)
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"class": "govuk-radios--conditional", "data-module":"radios"}
     * })
     * @Form\Options({
     *     "label_attributes": {
     *         "class":"govuk-label govuk-radios__label govuk-label--s"
     *     },
     *     "value_options":{
     *          "yes": {
     *              "label": "Yes",
     *              "value": "1",
     *              "attributes": {"data-aria-controls":"RestrictedCountriesList"},
     *          },
     *          "no": {
     *              "label": "No",
     *              "value": "0",
     *          },
     *     }
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     */
    public $restrictedCountries = null;

    /**
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\RestrictedCountriesList")
     */
    public $yesContent = null;

}
