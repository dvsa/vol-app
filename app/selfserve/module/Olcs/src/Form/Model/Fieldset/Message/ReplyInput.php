<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset\Message;

use Laminas\Filter\StringTrim;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;
use Laminas\Validator\StringLength;
use Laminas\Validator\NotEmpty;

class ReplyInput
{
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
     * @Form\Validator(NotEmpty::class,
     *     options={
     *         "messages":{NotEmpty::IS_EMPTY: "messaging.form.message.content.empty.error_message"},
     *     },
     *     breakChainOnFailure=true
     *  )
     * @Form\Validator(StringLength::class,
     *     options={
     *         "min": 5,
     *         "max": 1000,
     *         "messages": {
     *              StringLength::TOO_SHORT: "messaging.form.message.content.too_short.error_message",
     *              StringLength::TOO_LONG: "messaging.form.message.content.too_long.error_message",
     *          }
     *     }
     * )
     */
    public ?TextArea $reply = null;
}
