<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("main")
 */
class Conversation
{
    /**
     * @Form\Attributes({"id":"subject","placeholder":""})
     * @Form\Options({
     *     "label": "messaging.create-conversation.subject",
     *     "service_name": Common\Service\Data\MessagingSubject::class,
     *     "empty_option": "Please Select"
     * })
     * @Form\Type("DynamicSelect")
     */
    public $messageSubject = null;

     /**
     * @Form\Attributes({"id":"appOrLicNo","placeholder":""})
     * @Form\Options({
     *     "label": "Application or licence ID",
     *     "empty_option": "Please Select",
     * })
     * @Form\Type("Select")
     */
    public $appOrLicNo = null;

    /**
     * @Form\Attributes({"class":"extra-long","id":""})
     * @Form\Options({"label":"Message"})
     * @Form\Type("TextArea")
     * @Form\Filter(\Laminas\Filter\StringTrim::class)
     * @Form\Validator(\Laminas\Validator\StringLength::class, options={"min": 5, "max": 1000})
     */
    public $messageContent = null;

    /**
     * @Form\Type("Hidden")
     * @Form\Options({"value": ""})
     */
    public $licence = null;

    /**
     * @Form\Type("Hidden")
     * @Form\Options({"value": ""})
     */
    public $application = null;

}
