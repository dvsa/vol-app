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
     */
    public ?DynamicSelect $messageSubject = null;

    /**
     * @Form\Attributes({"class": "extra-long", "id": ""})
     * @Form\Options({"label": "Message"})
     * @Form\Type(Textarea::class)
     * @Form\Filter(StringTrim::class)
     * @Form\Validator(StringLength::class, options={"min": 5, "max": 1000})
     */
    public ?Textarea $messageContent = null;
}
