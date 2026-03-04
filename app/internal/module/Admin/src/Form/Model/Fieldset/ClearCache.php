<?php

declare(strict_types=1);

namespace Admin\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("clear-cache")
 * @Form\Options({"label":"Select cache types to clear"})
 */
class ClearCache
{
    /**
     * @Form\Type("MultiCheckbox")
     * @Form\Required(false)
     * @Form\Attributes({
     *     "id": "cacheIds",
     *     "name": "cacheIds",
     * })
     * @Form\Options({
     *     "label": "Cache types",
     *     "hint": "Leave all unchecked to clear all caches.",
     *     "value_options": {
     *         "cqrs": "CQRS query caches",
     *         "sys_param": "System parameters",
     *         "sys_param_list": "System parameter list",
     *         "translation_key": "Translation keys",
     *         "translation_replacement": "Translation replacements",
     *         "user_account": "User account data",
     *         "storage": "Generic storage",
     *         "secretsmanager": "Secrets manager",
     *     },
     * })
     */
    public $cacheIds = null;
}
