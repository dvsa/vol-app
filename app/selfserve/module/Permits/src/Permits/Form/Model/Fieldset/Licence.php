<?php

namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Licence")
 * @Form\Type("\Common\Form\Elements\Types\RadioVertical")
 * @Form\Options({
 *     "radio-element": "licence"
 * })
 */
class Licence
{
    /**
     * @Form\Attributes({
     *     "radios_wrapper_attributes": {"class": "govuk-radios--conditional", "data-module":"radios"}
     * })
     * @Form\Options({
     *     "label_attributes": {
     *         "class":"govuk-label govuk-radios__label govuk-label--s"
     *     },
     * })
     * @Form\Type("\Common\Form\Elements\Types\Radio")
     */
    public $licence = null;
}
