<?php

declare(strict_types=1);

namespace Admin\Form\Model\Form\Letter;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("letter-section-variant-edit")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class LetterSectionVariantEdit
{
    /**
     * @Form\Name("letterSectionVariant")
     * @Form\ComposedObject("Admin\Form\Model\Fieldset\Letter\LetterSectionVariantEdit")
     */
    public $letterSectionVariant = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
