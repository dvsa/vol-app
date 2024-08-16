<?php

declare(strict_types=1);

namespace Olcs\Form\Model\Fieldset\Message;

use Common\Form\Model\Fieldset\MultipleFileUpload;
use Laminas\Form\Annotation as Form;

class Reply
{
    /**
     * @Form\Name("inputs")
     * @Form\Attributes({"id": "inputs"})
     * @Form\ComposedObject(ReplyInput::class)
     */
    public ?ReplyInput $inputs = null;

    /**
     * @Form\Name("file")
     * @Form\Attributes({"id": "file"})
     * @Form\ComposedObject(MultipleFileUpload::class)
     */
    public ?MultipleFileUpload $file = null;

    /**
     * @Form\Name("actions")
     * @Form\Attributes({"id": "actions"})
     * @Form\ComposedObject(Actions::class)
     */
    public ?Actions $send = null;
}
