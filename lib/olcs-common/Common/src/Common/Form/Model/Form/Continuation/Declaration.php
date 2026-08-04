<?php

namespace Common\Form\Model\Form\Continuation;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"method":"post"})
 * @Form\Type("\Common\Form\Form")
 */
class Declaration
{
    /**
     * @Form\Type("Hidden")
     */
    public $version;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Form\Continuation\Fieldset\DeclarationContent")
     */
    public $content;

    /**
     * @Form\ComposedObject("\Common\Form\Model\Form\Continuation\Fieldset\DeclarationSignatureDetails")
     */
    public $signatureDetails;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("\Common\Form\Model\Form\Continuation\Fieldset\DeclarationFormActions")
     */
    public $formActions;
}
