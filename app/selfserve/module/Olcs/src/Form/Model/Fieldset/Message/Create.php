<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset\Message;

use Common\Form\Element\DynamicSelect;
use Common\Form\Elements\InputFilters\ActionButton;
use Common\Form\Elements\Types\GuidanceTranslated;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;

class Create
{
    /**
     * @Form\Options({
     *     "label": "messaging.subject",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "service_name": \Common\Service\Data\MessagingSubject::class
     * })
     * @Form\Attributes({
     *     "class": "govuk-select"
     * })
     * @Form\Type(\Common\Form\Element\DynamicSelect::class)
     * @Form\Required(true)
     */
    public ?DynamicSelect $messageSubject = null;

    /**
     * @Form\Options({
     *     "label": "messaging.app-or-lic-no",
     *     "empty_option": "Please select",
     *     "disable_inarray_validator": false,
     *     "service_name": \Olcs\Service\Data\MessagingAppOrLicNo::class,
     *     "use_groups": true
     * })
     * @Form\Attributes({
     *     "class": "govuk-select"
     * })
     * @Form\Type(\Common\Form\Element\DynamicSelect::class)
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
     * @Form\Type(\Laminas\Form\Element\Textarea::class)
     * @Form\Filter(\Laminas\Filter\StringTrim::class)
     * @Form\Validator(\Laminas\Validator\StringLength::class, options={"min": 5, "max": 1000})
     */
    public ?TextArea $messageContent = null;

    /**
     * @Form\Attributes({"value": "markup-messaging-new-conversation-timeframe"})
     * @Form\Type(\Common\Form\Elements\Types\GuidanceTranslated::class)
     */
    public ?GuidanceTranslated $guidance = null;

    /**
     * @Form\Attributes({
     *     "type": "submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button govuk-button--default",
     *     "id": "send"
     * })
     * @Form\Options({
     *     "label": "Send message",
     * })
     * @Form\Type(\Common\Form\Elements\InputFilters\ActionButton::class)
     */
    public ?ActionButton $create = null;
}
