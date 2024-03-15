<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset\Message;

use Laminas\Filter\StringTrim;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;
use Laminas\Validator\StringLength;

class ReplyInput
{
    /**
     * @Form\Attributes({
     *     "class": "extra-long",
     *     "maxlength": 1000
     * })
     * @Form\Options({
     *     "label": "",
     *     "hint": "You can enter up to 1000 characters"
     * })
     * @Form\Required(true)
     * @Form\Type(Textarea::class)
     * @Form\Filter(StringTrim::class)
     * @Form\Validator(StringLength::class, options={"min": 5, "max": 1000})
     */
    public ?TextArea $reply = null;
}
