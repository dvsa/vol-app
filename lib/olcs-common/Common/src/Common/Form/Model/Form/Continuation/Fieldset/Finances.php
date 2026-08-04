<?php

namespace Common\Form\Model\Form\Continuation\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * Class Finances
 */
class Finances
{
    /**
     * @Form\Type("hidden")
     */
    public $version;

    /**
     * @Form\Type("Text")
     * @Form\Attributes({"id":"averageBalance"})
     * @Form\Options({
     *     "label":"continuations.finances.averageBalance.label",
     *     "hint":"continuations.finances.averageBalance.hint",
     *     "label_attributes": {"class": "form-element__question"},
     *     "hint-below": "markup-continuation-finances-average-balance",
     * })
     * @Form\Validator("NotEmpty", options={
     *     "messages": {"isEmpty" : "continuations.finances.averageBalance.empty"},
     *     "break_chain_on_failure": true,
     *})
     * @Form\Validator("Dvsa\Olcs\Transfer\Validators\Money", options={
     *     "allow_negative" : true,
     *     "messages": {
     *          "invalid": "continuations.finances.averageBalance.notNumber"
     *     }
     *})
     * @Form\Validator("GreaterThan", options={
     *     "min": -10000000000,
     *     "messages": {
     *         "notGreaterThan": "continuations.finances.averageBalance.notGreaterThan"
     *     }
     *})
     * @Form\Validator("LessThan", options={
     *     "max": 10000000000,
     *     "messages": {
     *         "notLessThan": "continuations.finances.averageBalance.notLessThan"
     *     }
     *})
     */
    public $averageBalance;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\OverdraftFacility")
     */
    public $overdraftFacility;

    /**
     * @Form\ComposedObject("Common\Form\Model\Form\Continuation\Fieldset\FinancesFactoring")
     * @Form\Options({"label": "continuations.finances.factoring.label"})
     */
    public $factoring;
}
