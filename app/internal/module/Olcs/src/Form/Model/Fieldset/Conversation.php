<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Element\DynamicSelect;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;
use Laminas\Filter\StringTrim;
use Laminas\Validator\StringLength;
use Common\Service\Data\MessagingSubject;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("main")
 */
class Conversation
{
    /**
     * @Form\Attributes({"id": "subject","placeholder": ""})
     * @Form\Options({
     *     "label": "messaging.create-conversation.subject",
     *     "service_name": MessagingSubject::class,
     *     "empty_option": "Please Select"
     * })
     * @Form\Type(DynamicSelect::class)
     * @Form\Validator("Laminas\Validator\NotEmpty",
     *         options={
     *             "messages":{Laminas\Validator\NotEmpty::IS_EMPTY:"messaging.form.message.subject.empty.error_message"},
     *         },
     *         breakChainOnFailure=true,
     *         priority=100,
     *    )
     */
    public ?DynamicSelect $messageSubject = null;

    /**
     * @Form\Attributes({
     *     "class": "extra-long",
     *     "id": "",
     * })
     * @Form\Options({
     *     "label": "You can enter up to 1000 characters",
     * })
     * @Form\Type(Textarea::class)
     * @Form\Filter(StringTrim::class)
     * @Form\Validator("Laminas\Validator\NotEmpty",
     *     options={
     *          "messages":{Laminas\Validator\NotEmpty::IS_EMPTY:"messaging.form.message.content.empty.error_message"},
     *     },
     *     breakChainOnFailure=true
     *  )
     * @Form\Validator(\Laminas\Validator\StringLength::class,
     *     options={
     *         "min": 5,
     *         "max": 1000,
     *         "messages": {
     *              Laminas\Validator\StringLength::TOO_SHORT:"messaging.form.message.content.too_short.error_message",
     *              Laminas\Validator\StringLength::TOO_LONG:"messaging.form.message.content.too_long.error_message",
     *          }
     *     }
     * )
     */
    public ?Textarea $messageContent = null;
}
