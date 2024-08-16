<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Elements\InputFilters\ActionButton;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Attributes({"class": "govuk-button-group"})
 */
class EnableConversationsActions
{
    /**
     * @Form\Attributes({
     *     "type": "submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button govuk-button--success",
     *     "id": "close"
     * })
     * @Form\Options({
     *     "label": "Enable messaging"
     * })
     * @Form\Type(\Common\Form\Elements\InputFilters\ActionButton::class)
     */
    public ?ActionButton $close = null;
}
