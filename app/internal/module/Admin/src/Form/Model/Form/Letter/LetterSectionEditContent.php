<?php

declare(strict_types=1);

namespace Admin\Form\Model\Form\Letter;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("letter-section-edit-content")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class LetterSectionEditContent
{
    /**
     * @Form\Name("letterSectionEditContent")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\Letter\LetterSectionEditContent")
     */
    public $letterSectionEditContent = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
