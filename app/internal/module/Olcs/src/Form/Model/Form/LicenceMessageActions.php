<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Hidden;
use Olcs\Form\Model\Fieldset\LicenceMessageActions as LicenceMessageActionsFieldset;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("licence_message_actions")
 * @Form\Attributes({"method": "post", "class": "table__form"})
 * @Form\Type(\Common\Form\Form::class)
 * @Form\Options({"prefer_form_input_filter": true})
 */
class LicenceMessageActions
{
    /**
     * @Form\Attributes({"value": ""})
     * @Form\Type("Hidden")
     */
    public ?Hidden $id = null;

    /**
     * @Form\Attributes({"value": "end and archive conversation"})
     * @Form\Type("Hidden")
     */
    public ?Hidden $action = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\LicenceMessageActions::class)
     */
    public ?LicenceMessageActionsFieldset $formActions = null;
}
