<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

class LongTextAddDetails
{
    /**
     * @Form\Options({
     *     "label": "UID",
     *     "hint": "Lowercase words separated by hyphens, e.g. application-declaration-gv79-gb. Used by developers to place this content on a page and cannot be changed later."
     * })
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"id":"referenceKey", "class":"long"})
     * @Form\Filter("Laminas\Filter\StringTrim")
     * @Form\Validator("Laminas\Validator\Regex", options={"pattern":"/^[a-z0-9]+(-[a-z0-9]+)*$/", "messages":{"regexNotMatch":"UID must be lowercase words separated by single hyphens"}})
     */
    public $referenceKey = null;

    /**
     * @Form\Options({
     *     "label": "Language and region",
     *     "value_options": {
     *         "en_GB": "English (GB)",
     *         "cy_GB": "Welsh (GB)",
     *         "en_NI": "English (Northern Ireland)",
     *         "cy_NI": "Welsh (Northern Ireland)"
     *     }
     * })
     * @Form\Required(true)
     * @Form\Type("Select")
     * @Form\Attributes({"id":"locale", "value":"en_GB"})
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
