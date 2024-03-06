<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Elements\InputFilters\ActionButton;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("main")
 * @Form\Attributes({"class": "govuk-button-group"})
 */
class CloseConversationActions
{
    /**
     * @Form\Attributes({
     *     "type": "submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button govuk-button--warning",
     *     "id": "close"
     * })
     * @Form\Options({
     *     "label": "End and archive conversation"
     * })
     * @Form\Type(ActionButton::class)
     */
    public ?ActionButton $close = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "cancel",
     *     "class": "govuk-link action-button-link",
     *     "id": "cancel"
     * })
     * @Form\Options({
     *     "label": "Cancel"
     * })
     * @Form\Type(ActionButton::class)
     */
    public ?ActionButton $cancel = null;
}
