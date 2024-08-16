<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Form\Message;

use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Hidden;
use Olcs\Form\Model\Fieldset\Message\Create as CreateFieldset;

/**
 * @Form\Type("Common\Form\Form")
 */
class Create
{
    /**
     * @Form\Attributes({"value": ""})
     * @Form\Type(Hidden::class)
     */
    public ?Hidden $correlationId = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject(CreateFieldset::class)
     */
    public ?CreateFieldset $formActions = null;
}
