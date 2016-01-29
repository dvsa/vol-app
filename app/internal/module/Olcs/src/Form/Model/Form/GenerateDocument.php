<?php

namespace Olcs\Form\Model\Form;

use Zend\Form\Annotation as Form;

/**
 * @Form\Name("generate-document")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class GenerateDocument
{
    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * @Form\Attributes({"value":""})
     * @Form\Type("Hidden")
     */
    public $version = null;

    /**
     * @Form\Name("details")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\GenerateDocumentDetails")
     */
    public $details = null;

    /**
     * @Form\Name("bookmarks")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\GenerateDocumentBookmarks")
     */
    public $bookmarks = null;

    /**
     * @Form\Name("form-actions")
     * @Form\ComposedObject("Olcs\Form\Model\Fieldset\GenerateDocumentActions")
     */
    public $formActions = null;
}
