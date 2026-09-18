<?php

namespace Admin\Form\Model\Form;

use Laminas\Form\Annotation as Form;
use Olcs\Form\Model\Fieldset\Base;

/**
 * @Form\Name("CacheClear")
 * @Form\Attributes({"method":"post"})
 * @Form\Type("Common\Form\Form")
 * @Form\Options({"prefer_form_input_filter": true})
 */
class CacheClear extends Base
{
    /**
     * @Form\Type("MultiCheckbox")
     * @Form\Required(true)
     * @Form\Attributes({
     *     "id":"cacheTypes",
     *     "name":"cacheTypes"
     * })
     * @Form\Options({
     *     "label":"Select the caches to clear",
     *     "value_options":{
     *         "translations":"Translations",
     *         "system_parameters":"System parameters",
     *         "cqrs":"Feature toggles and front-end CQRS cache",
     *         "doctrine":"Doctrine ORM (entity metadata, query and result cache)",
     *         "jwks":"Cognito signing keys (JWKS)"
     *     }
     * })
     */
    public $cacheTypes = null;

    /**
     * @Form\Name("form-actions")
     * @Form\Attributes({"class":"govuk-button-group"})
     * @Form\ComposedObject("Common\Form\Model\Form\Lva\Fieldset\FormCrudActions")
     */
    public $formActions = null;
}
