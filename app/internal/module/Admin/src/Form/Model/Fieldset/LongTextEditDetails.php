<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

class LongTextEditDetails
{
    /**
     * @Form\Type("Hidden")
     */
    public $id = null;

    /**
     * PlainText does not retain populated values (OLCS-17989).
     *
     * @Form\Options({"label": "UID", "hint": "Used by developers to place this content on a page. It cannot be changed."})
     * @Form\Type("Text")
     * @Form\Attributes({"id":"referenceKey", "class":"long", "readonly":true})
     */
    public $referenceKey = null;

    /**
     * @Form\Options({"label": "Language and region"})
     * @Form\Type("Text")
     * @Form\Attributes({"id":"locale", "readonly":true})
     */
    public $locale = null;

    /**
     * @Form\Options({"label": "Page name"})
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"id":"pageName", "class":"long"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"min":1, "max":255})
     */
    public $pageName = null;

    /**
     * @Form\Options({"label": "Description"})
     * @Form\Required(false)
     * @Form\Type("Textarea")
     * @Form\Attributes({"id":"description", "class":"extra-long", "rows":3})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\StringLength", options={"max":1024})
     */
    public $description = null;

    /**
     * @Form\Options({"label": "Content"})
     * @Form\Required(true)
     * @Form\Type("EditorJs")
     * @Form\Attributes({
     *     "id":"longTextContent",
     *     "class":"extra-long",
     *     "name":"content",
     *     "data-placeholder":"Enter the wording for this page...",
     *     "data-tools-profile":"govuk-long-text"
     * })
     * @Form\Filter("Laminas\Filter\StringTrim")
     */
    public $content = null;
}
