<?php
namespace Permits\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("restrictedCountries")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Permits\Form\Form")
 */
class RestrictedCountriesForm
{
    /**
     * @Form\Name("Fields")
     * @Form\Options({
     *     "label" : "permits.page.field.restricted-countries",
     *     "hint" : "TEST",
     *     "hint" : "TEST1",
     *     "hint" : "TEST2",
     * })
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\RestrictedCountries")
     */
    public $fields = null;

    /**
     * @Form\Name("Submit")
     * @Form\ComposedObject("Permits\Form\Model\Fieldset\Submit")
     */
    public $submitButton = null;


}
