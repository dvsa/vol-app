<?php

namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 */
class QaSingleCheckbox
{
    /**
     * @Form\Name("qaElement")
     * @Form\Attributes({
     *   "class" : "input--qasinglecheckbox",
     *   "id" : "qaElement",
     * })
     * @Form\Options({
     *     "checked_value": "1",
     *     "unchecked_value": "0",
     *     "label": "change.me",
     *     "label_attributes": {"class": "form-control form-control--checkbox form-control--advanced"},
     *     "must_be_value": "1",
     *     "not_checked_message": "change.me"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $qaSingleCheckbox = null;
}
