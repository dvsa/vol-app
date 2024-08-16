<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Hidden;
use Olcs\Form\Model\Fieldset\LicenceMessageReply as LicenceMessageReplyFieldset;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("licence_message_reply")
 * @Form\Attributes({"method": "post"})
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class LicenceMessageReply
{
    /**
     * @Form\Attributes({"value": ""})
     * @Form\Type("Hidden")
     */
    public ?Hidden $id = null;

    /**
     * @Form\Attributes({"value": "reply"})
     * @Form\Type("Hidden")
     */
    public ?Hidden $action = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\LicenceMessageReply::class)
     */
    public ?LicenceMessageReplyFieldset $formActions = null;
}
