<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

#[Form\Name('finance')]
class FinancialEvidenceFinance
{
    /**
     * (value is set by individual LVA adapters)
     */
    #[Form\Type('\Common\Form\Elements\Types\HtmlTranslated')]
    public $requiredFinance;
}
