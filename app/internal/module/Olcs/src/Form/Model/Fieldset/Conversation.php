<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Element\DynamicSelect;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Select;
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
     * @Form\Attributes({"id": "appOrLicNo","placeholder": ""})
     * @Form\Options({
     *     "label": "Application or licence ID",
     *     "empty_option": "Please Select",
     * })
     * @Form\Type(Select::class)
     */
    public ?Select $appOrLicNo = null;

    /**
     * @Form\Attributes({"class": "extra-long","id": ""})
     * @Form\Options({"label": "Message"})
     * @Form\Type(Textarea::class)
     * @Form\Filter(StringTrim::class)
     * @Form\Validator(StringLength::class, options={"min": 5, "max": 1000})
     */
    public ?Textarea $messageContent = null;

    /**
     * @Form\Type(Hidden::class)
     * @Form\Options({"value": ""})
     */
    public ?Hidden $licence = null;

    /**
     * @Form\Type(Hidden::class)
     * @Form\Options({"value": ""})
     */
    public ?Hidden $application = null;
}
