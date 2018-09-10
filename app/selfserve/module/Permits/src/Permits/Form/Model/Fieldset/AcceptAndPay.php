<?php
namespace Permits\Form\Model\Fieldset;

use Zend\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("AcceptAndPay")
 * @Form\Attributes({"class":"actions-container"})
 */
class AcceptAndPay
{
    /**
     * @Form\Name("SubmitButton")
     * @Form\Attributes({"id":"submit-accept-button","type":"submit","class":"action--primary action--external large"})
     * @Form\Options({"label": "permits.page.accept.and.pay"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit = null;

    /**
     * @Form\Name("DeclineButton")
     *
     * @Form\Attributes({"id":"submit-decline-button","type":"submit","class":"action--secondary large"})
     * @Form\Options({"label": "permits.page.decline.permits"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $decline = null;
}
