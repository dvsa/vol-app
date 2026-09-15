<?php

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({"label": "application_previous-history_financial-history.confirmation"})
 */
class FinancialHistoryConfirmation
{
    /**
     * @Form\Attributes({"id":""})
     * @Form\Options({
     *     "short-label": "short-label-financial-history-insolvency",
     *     "checked_value": "Y",
     *     "unchecked_value": "N",
     *     "label": "application_previous-history_financial-history.insolvencyConfirmation.title",
     *     "label_attributes": {
     *         "class": "form-control form-control--checkbox form-control--advanced",
     *         "id":"insolvency"
     *     },
     *     "must_be_value": "Y"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\SingleCheckbox")
     */
    public $insolvencyConfirmation;
}
