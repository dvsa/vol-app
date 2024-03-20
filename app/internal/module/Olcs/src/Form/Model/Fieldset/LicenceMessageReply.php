<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Elements\InputFilters\ActionButton;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;
use Laminas\Validator\StringLength;
use Laminas\Validator\NotEmpty;

class LicenceMessageReply
{
    /**
     * @Form\Attributes({
     *     "class": "extra-long",
     *     "maxlength": 1000
     * })
     * @Form\Options({
     *     "label": "You can enter up to 1000 characters",
     * })
     * @Form\Type(\Laminas\Form\Element\Textarea::class)
     * @Form\Filter(\Laminas\Filter\StringTrim::class)
     * @Form\Validator(NotEmpty::class,
     *     options={
     *         "messages": {
     *             NotEmpty::IS_EMPTY: "messaging.form.message.content.empty.error_message"
     *         },
     *     },
     *     breakChainOnFailure=true
     *   )
     * @Form\Validator(StringLength::class,
     *      options={
     *          "min": 5,
     *          "max": 1000,
     *          "messages": {
     *               StringLength::TOO_SHORT: "messaging.form.message.content.too_short.error_message",
     *               StringLength::TOO_LONG: "messaging.form.message.content.too_long.error_message",
     *           }
     *      }
     *  )
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
