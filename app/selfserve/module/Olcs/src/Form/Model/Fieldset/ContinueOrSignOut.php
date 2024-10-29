<?php

namespace Olcs\Form\Model\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @codeCoverageIgnore Auto-generated file with no methods
 * @Form\Name("form-actions")
 * @Form\Attributes({"class":"govuk-button-group"})
 */
class ContinueOrSignOut
{
    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     *     "id":"submit",
     * })
     * @Form\Options({"label": "Save and continue"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $submit;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button govuk-button--secondary",
     *     "id":"signout",
     * })
     * @Form\Options({"label": "selfserve-dashboard-topnav-sign-out"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $signOut;
}
