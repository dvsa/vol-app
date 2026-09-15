<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("guidance")
 */
class Guidance
{
    /**
     * @Form\Attributes({"value":"<div class=guidance>%s</div>"})
     * @Form\Options({"tokens":{"selfserve-app-subSection-your-business-people-guidance"}})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $guidance;
}
