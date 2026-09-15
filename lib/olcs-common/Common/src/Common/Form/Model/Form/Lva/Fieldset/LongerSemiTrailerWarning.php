<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Name("lva-longer-semi-trailer-warning")
 */
class LongerSemiTrailerWarning
{
    /**
     * @Form\Attributes({"value": "markup-lva-trailers-longerSemiTrailersWarningHtml"})
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     */
    public $longerSemiTrailerWarning;
}
