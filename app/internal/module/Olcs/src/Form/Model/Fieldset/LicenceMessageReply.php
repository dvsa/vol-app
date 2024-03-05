<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Elements\InputFilters\ActionButton;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\File;
use Laminas\Form\Element\Textarea;

class LicenceMessageReply
{
    /**
     * @Form\Attributes({
     *     "class": "extra-long",
     *     "maxlength": 1000
     * })
     * @Form\Options({
     *     "label": "You can enter up to 1000 characters",
     *     "error-message": "Value is required and must be between 5 and 1000 characters."
     * })
     * @Form\Required(true)
     * @Form\Type(\Laminas\Form\Element\Textarea::class)
     * @Form\Filter(\Laminas\Filter\StringTrim::class)
     * @Form\Validator(\Laminas\Validator\StringLength::class, options={"min": 5, "max": 1000})
     */
    public ?TextArea $reply = null;

    /**
     * @Form\Attributes({
     *     "type": "submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button govuk-button--default",
     *     "id": "send"
     * })
     * @Form\Options({
     *     "label": "Send message"
     * })
     * @Form\Type(\Common\Form\Elements\InputFilters\ActionButton::class)
     */
    public ?ActionButton $send = null;
}
