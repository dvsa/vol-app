<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("SubmissionSectionEditComment")
 * @Form\Attributes({"method":"post"})
 */
class SubmissionSectionEditComment
{
    /**
     * @Form\Name("fields")
     * @Form\Options({"label":"","class":""})
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\SubmissionSectionEditComment")
     */
    public $fields = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"actions-container"})
     * @Form\ComposedObject("Common\Form\Model\Fieldset\CancelFormActions")
     */
    public $formActions = null;
}
