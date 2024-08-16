<?php

namespace Permits\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("Submit")
 */
class AcceptAndPay
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({"id":"submit-accept-button","type":"submit","class":"govuk-button govuk-!-margin-right-1"})
     * @Form\Options({"label": "permits.page.accept.and.pay"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Name("RemoveUnwantedPermitButton")
     * @Form\Attributes({"id":"remove-unwanted-permit-button","type":"submit","class":"govuk-button govuk-!-margin-right-1"})
     * @Form\Options({"label": "permits.page.remove.unwanted.permit"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $choosePermits = null;

    /**
     * @Form\Name("DeclineButton")
     *
     * @Form\Attributes({"id":"submit-decline-button","type":"submit","class":"govuk-button govuk-button--secondary"})
     * @Form\Options({"label": "permits.page.decline.permits"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $decline = null;
}
