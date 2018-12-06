<?php

namespace Olcs\Form\Model\Form\Surrender\CurrentDiscs\Fieldset;

use Zend\Form\Annotation as Form;

class StolenInfo
{
    /**
     * @Form\Type("Number")
     * @Form\Options({
     *     "label":"Number of discs stolen",
     * })
     * @Form\Attributes({
     *      "class":"govuk-input govuk-!-width-one-third"
     * })
     */
    public $numberStolen = null;

    /**
     * @Form\Type("textarea")
     * @Form\Options({
     *     "label":"Please provide details of stolen documents",
     *     "hint":"Don’t include personal or financial information, eg your National Insurance number or credit card details."
     * })
     * @Form\Attributes({
     *     "class":"govuk-textarea",
     *     "rows":"5"
     * })
     */
    public $stolenDetails = null;
}
