<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset\Message;

use Common\Form\Element\DynamicSelect;
use Common\Service\Data\MessagingSubject;
use Laminas\Filter\StringTrim;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;
use Laminas\Validator\StringLength;
use Olcs\Service\Data\MessagingAppOrLicNo;

class CreateInput
{
    /**
     * @Form\Options({
     *     "label": "messaging.subject",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "service_name": MessagingSubject::class
     * })
     * @Form\Attributes({
     *     "class": "govuk-select"
     * })
     * @Form\Type(DynamicSelect::class)
     * @Form\Required(true)
     */
    public ?DynamicSelect $messageSubject = null;

    /**
     * @Form\Options({
     *     "label": "messaging.app-or-lic-no",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "service_name": MessagingAppOrLicNo::class,
     *     "use_groups": true
     * })
     * @Form\Attributes({
     *     "class": "govuk-select"
     * })
     * @Form\Type(DynamicSelect::class)
     * @Form\Required(true)
     */
    public ?DynamicSelect $appOrLicNo = null;

    /**
     * @Form\Attributes({
     *     "class": "extra-long",
     *     "maxlength": 1000
     * })
     * @Form\Options({
     *     "label": "",
     *     "hint": "You can enter up to 1000 characters"
     * })
     * @Form\Required(true)
     * @Form\Type(Textarea::class)
     * @Form\Filter(StringTrim::class)
     * @Form\Validator(StringLength::class, options={"min": 5, "max": 1000})
     */
    public ?Textarea $messageContent = null;
}
