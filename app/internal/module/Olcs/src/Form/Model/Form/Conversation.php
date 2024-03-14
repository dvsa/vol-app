<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Common\Form\Model\Fieldset\CreateConversationFormActions;
use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Conversation as ConversationFieldset;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Conversation")
 * @Form\Attributes({"method": "post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Conversation
{
    /**
     * @Form\Name("fields")
     * @Form\ComposedObject(ConversationFieldset::Class)
     */
    public ?ConversationFieldset $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject(CreateConversationFormActions::class)
     */
    public ?CreateConversationFormActions $formActions = null;
}
