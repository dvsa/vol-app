<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Elements\Types\PlainText;
use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 */
class EnableConversationsPopupText
{
    /**
     * @Form\Type(\Common\Form\Elements\Types\PlainText::class)
     * @Form\Attributes({
     *     "value": "Messaging will be enabled for this user."
     * })
     */
    public ?PlainText $text = null;
}
