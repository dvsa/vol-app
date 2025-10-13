<?php

declare(strict_types=1);

namespace Admin\Form\Model\Form\Letter;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("letter-issue")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class LetterIssue
{
    /**
     * @Form\Name("letterIssue")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\Letter\LetterIssue")
     */
    public $letterIssue = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}