<?php

namespace Olcs\Form\Model\Fieldset\Lva;

use Common\Form\Model\Form\Traits as FormTraits;
use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("phone-contact-details")
 */
class PhoneContact
{
    use FormTraits\IdTrait;
    use FormTraits\VersionTrait;

    /**
     * @Form\Required(true)
     * @Form\Attributes({"id":"","placeholder":""})
     * @Form\Options({
     *     "label": "lva.contact-details.phone-contact.form.type.title",
     *     "disable_inarray_validator": false,
     *     "category": "phone_contact_type",
     *     "exclude": {"phone_t_gtn"},
     * })
     * @Form\Type("DynamicRadio")
     */
    public $phoneContactType = null;

    /**
     * @Form\Required(true)
     * @Form\Attributes({
     *     "id": "number",
     *     "placeholder": "",
     *     "class": "medium",
     *     "required": true
     * })
     * @Form\Input("Common\InputFilter\ContinueIfEmptyInput")
     * @Form\Options({
     *     "label": "lva.contact-details.phone-contact.form.number.title",
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\Phone")
     */
    public $phoneNumber = null;

    /**
     * @Form\Type("Hidden")
     */
    public $contactDetailsId = null;
}
