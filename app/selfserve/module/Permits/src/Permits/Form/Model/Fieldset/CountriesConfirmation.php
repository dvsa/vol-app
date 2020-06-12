<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("CountriesConfirmation")
 */
class CountriesConfirmation
{
    /**
     * @Form\Name("countries")
     * @Form\Type("Hidden")
     */
    public $countries = null;

    /**
     * @Form\Name("removedCountries")
     * @Form\Type("\Common\Form\Elements\Types\Html")
     */
    public $removedCountries = null;
    
    /**
     * @Form\Name("confirmation")
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "permits.page.bilateral.countries-confirmation.label",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1",
     *     "not_checked_message": "permits.page.bilateral.countries-confirmation.error"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $confirmation = null;
}
