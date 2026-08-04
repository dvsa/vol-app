<?php

namespace Common\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("messages")
 */
class Messages
{
    /**
     * @Form\Name("message")
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    public $message;
}
