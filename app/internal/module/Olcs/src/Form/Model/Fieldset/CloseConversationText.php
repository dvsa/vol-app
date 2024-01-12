<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("main")
 */
class CloseConversationText
{
    /**
     * @Form\Type(\Common\Form\Elements\Types\PlainText::class)
     * @Form\Attributes({
     *     "value": "The conversation will be removed from the Inbox and a transcript will be archived in docs and attachments tab."
     * })
     */
    public ?PlainText $text = null;
}
