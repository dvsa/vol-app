<?php

namespace Olcs\Form\Model\Form\Surrender;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Attributes({"id":"surrender-information-changed"})
 * @Form\Type("Common\Form\Form")
 */
class InformationChanged
{
    /**
     * @Form\Attributes({"type":"submit","class":"govuk-button govuk-button--start"})
     * @Form\Options({"label":"licence.surrender.information_changed.start_again"})
     * @Form\Type("\Laminas\Form\Element\Button")
     */
    public $startAgain = null;

    /**
     * @Form\Attributes({
     *     "data-module": "govuk-button",
     *     "type": "submit",
     *     "class": "govuk-button",
     * })
     * @Form\Options({"label":"licence.surrender.information_changed.review_continue"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $reviewAndContinue = null;
}
