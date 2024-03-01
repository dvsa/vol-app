<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset\Message;

use Common\Form\Elements\InputFilters\ActionButton;
use Common\Form\Model\Fieldset\MultipleFileUpload;
use Laminas\Form\Annotation as Form;
use Laminas\Form\Element\Textarea;
use Laminas\Filter\StringTrim;
use Laminas\Validator\StringLength;

class Reply
{
    /**
     * @Form\Attributes({
     *     "class": "extra-long",
     *     "maxlength": 1000
     * })
     * @Form\Options({"label": "You can enter up to 1000 characters"})
     * @Form\Required(true)
     * @Form\Type(Textarea::class)
     * @Form\Filter(StringTrim::class)
     * @Form\Validator(StringLength::class, options={"min": 5, "max": 1000})
     */
    public ?TextArea $reply = null;

    /**
     * @Form\Name("file")
     * @Form\Attributes({"id": "file"})
     * @Form\ComposedObject(MultipleFileUpload::class)
     */
    public ?MultipleFileUpload $file = null;

    /**
     * @Form\Attributes({
     *     "type": "submit",
     *     "data-module": "govuk-button",
     *     "class": "govuk-button govuk-button--default",
     *     "id": "send"
     * })
     * @Form\Options({
     *     "label": "Send message"
     * })
     * @Form\Type(ActionButton::class)
     */
    public ?ActionButton $send = null;
}
