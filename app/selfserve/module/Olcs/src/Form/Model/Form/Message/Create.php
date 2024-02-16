<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Message;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Message\Reply as ReplyFieldset;

class Create
{
    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(\Olcs\Form\Model\Fieldset\Message\Create::class)
     */
    public ?ReplyFieldset $formActions = null;
}
