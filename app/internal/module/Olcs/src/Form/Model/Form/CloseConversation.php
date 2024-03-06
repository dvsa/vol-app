<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Hidden;
use Olcs\Form\Model\Fieldset\CloseConversationActions;
use Olcs\Form\Model\Fieldset\CloseConversationText;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("close_conversation")
 * @Form\Attributes({"method": "post"})
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class CloseConversation
{
    /**
     * @Form\Attributes({"value": ""})
     * @Form\Type("Hidden")
     */
    public ?Hidden $id = null;

    /**
     * @Form\Name("form-text")
     * @Form\ComposedObject(CloseConversationText::class)
     */
    public ?CloseConversationText $text = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(CloseConversationActions::class)
     */
    public ?CloseConversationActions $formActions = null;
}
