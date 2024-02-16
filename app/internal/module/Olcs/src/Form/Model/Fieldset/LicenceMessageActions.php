<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Elements\InputFilters\ActionButton;
use Laminas\Form\Annotation as Form;

class LicenceMessageActions
{
    /**
     * @Form\Attributes({
     *     "type": "submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button govuk-button--warning",
     *     "id": "close"
     * })
     * @Form\Options({
     *     "label": "End and Archive Conversation"
     * })
     * @Form\Type(\Common\Form\Elements\InputFilters\ActionButton::class)
     */
    public ?ActionButton $close = null;
}
