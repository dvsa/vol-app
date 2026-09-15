<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"label" : "continuations.insufficient-finances-summary.label"})
 */
class InsufficientFinancesSummary
{
    /**
     * @Form\Type("\Common\Form\Elements\Types\HtmlTranslated")
     * @Form\Attributes({"value": "markup-continuation-finances-overview"})
     */
    public $financialOverview;
}
