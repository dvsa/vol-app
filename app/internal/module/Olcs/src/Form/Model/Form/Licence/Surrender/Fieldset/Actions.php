<?php

namespace Olcs\Form\Model\Form\Licence\Surrender\Fieldset;

use Laminas\Form\Annotation as Form;

class Actions
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--disabled js-approve-surrender",
     * })
     * @Form\Options({"label": "Surrender"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $surrender = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary js-modal-ajax",
     * })
     * @Form\Options({
     *     "label": "Withdraw",
     *     "route": "licence/surrender-details/withdraw/GET"
     * })
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionLink")
     */
    public $withdraw = null;
}
