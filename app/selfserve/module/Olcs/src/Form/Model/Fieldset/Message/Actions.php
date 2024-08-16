<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset\Message;

use Common\Form\Elements\InputFilters\ActionButton;
use Common\Form\Elements\Types\GuidanceTranslated;
use Laminas\Form\Annotation as Form;

class Actions
{
    /**
     * @Form\Attributes({
     *     "value": "markup-messaging-new-conversation-timeframe",
     *     "id": "guidance"
     * })
     * @Form\Type(GuidanceTranslated::class)
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
     *     "label": "Send message"
     * })
     * @Form\Type(ActionButton::class)
     */
    public ?ActionButton $send = null;
}
