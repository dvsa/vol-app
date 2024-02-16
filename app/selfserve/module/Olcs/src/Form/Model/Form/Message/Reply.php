<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Message;

use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Hidden;
use Olcs\Form\Model\Fieldset\Message\Reply as ReplyFieldset;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("licence_message_reply")
 * @Form\Attributes({"method": "post"})
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class Reply
{
    /**
     * @Form\Attributes({"value": ""})
     * @Form\Type(\Laminas\Form\Element\Hidden::class)
     */
    public ?Hidden $id = null;

    /**
     * @Form\Attributes({"value": "reply"})
     * @Form\Type(\Laminas\Form\Element\Hidden::class)
     */
    public ?Hidden $action = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\Message\Reply::class)
     */
    public ?ReplyFieldset $formActions = null;
}
