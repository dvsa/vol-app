<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Zend\Form\Annotation as Form;

class Header
{
    /**
     * @Form\Options({
     *     "hint":"Select all options that are relevant to your discs."
     * })
     * @Form\Attributes({"value": "markup-licence-surrender-current-disc-form-header"})
     * @Form\Type("Common\Form\Elements\Types\HtmlTranslated")
     */
    protected $header = null;
}
