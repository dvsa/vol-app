<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore No methods
 * @Form\Name("Cpid-filter")
 * @Form\Attributes({"method":"get", "class":"form__filter filters"})
 * @Form\Type("Common\Form\Form")
 */
class TemplateFilter
{
    /**
     * @Form\Options({
     *     "label": "documents-home.data.category",
     *     "disable_inarray_validator": false,
     *     "service_name": "Olcs\Service\Data\EmailTemplateCategory",
     *     "empty_option": "All",
     * })
     * @Form\Type("DynamicSelect")
     */
    public $emailTemplateCategory = null;

    /**
     * Filter by template format. Fixed enum (html / plain / md) so a static Select is fine —
     * no need for a backend data service. Added by VOL-7238 alongside the new `format='md'`
     * rows so admins can narrow the list of ~180 rows to just the markdown ones.
     *
     * @Form\Options({
     *     "label": "Format",
     *     "empty_option": "All",
     *     "value_options": {
     *         "html": "HTML",
     *         "plain": "Plain Text",
     *         "md": "Markdown",
     *     },
     * })
     * @Form\Type("Select")
     */
    public $format = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({
     *     "label": "tasks.submit.filter"
     * })
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $filter = null;
}
