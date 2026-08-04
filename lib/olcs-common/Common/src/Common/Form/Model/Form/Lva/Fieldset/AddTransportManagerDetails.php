<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Add Transport Manager details fieldset
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddTransportManagerDetails
{
    /**
     * @Form\Attributes({"class":"long","id":"", "disabled":"disabled"})
     * @Form\Options({"label":"lva-tm-details-forename"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $forename;

    /**
     * @Form\Attributes({"class":"long","id":"", "disabled":"disabled"})
     * @Form\Options({"label":"lva-tm-details-familyName"})
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $familyName;

    /**
     * @Form\Attributes({"id":"dob"})
     * @Form\Options({
     *     "label": "dob",
     *     "create_empty_option": false,
     *     "render_delimiters": true
     * })
     * @Form\Type("DateSelect")
     * @Form\Filter("DateSelectNullifier")
     * @Form\Validator("\Common\Validator\Date")
     * @Form\Validator("Date", options={"format":"Y-m-d"})
     * @Form\Validator("DateNotInFuture")
     */
    public $birthDate;

    /**
     * @Form\Attributes({"class":"medium", "disabled":"disabled"})
     * @Form\Options({
     *     "label":"lva-tm-details-email",
     *     "label_attributes": {
     *         "aria-label": "Enter their email address"
     *     }
     * })
     * @Form\Type("Text")
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\EmailAddress")
     */
    public $email;

    /**
     * @Form\Attributes({"value": "markup-lva-tm-add-tm-details-guidance"})
     * @Form\Type("\Common\Form\Elements\Types\GuidanceTranslated")
     */
    public $guidance;
}
