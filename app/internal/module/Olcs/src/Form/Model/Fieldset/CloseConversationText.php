<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Common\Form\Elements\Types\PlainText;
use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("main")
 */
class CloseConversationText
{
    /**
     * @Form\Type(PlainText::class)
     * @Form\Attributes({
     *     "value": "messaging.close-conversation.popup"
     * })
     */
    public ?PlainText $text = null;
}
