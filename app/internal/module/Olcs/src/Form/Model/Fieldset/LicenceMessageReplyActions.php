<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Elements\InputFilters\ActionButton;
use Laminas\Form\Annotation as Form;

class LicenceMessageReplyActions
{
    #[Form\Attributes(['type' => 'submit', 'data-module' => 'govuk-button', 'class' => 'govuk-button govuk-button--default', 'id' => 'send'])]
    #[Form\Options(['label' => 'Send message'])]
    #[Form\Type(ActionButton::class)]
    public ?ActionButton $send = null;
}
