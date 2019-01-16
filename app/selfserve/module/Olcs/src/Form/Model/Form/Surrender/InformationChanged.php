<?php

namespace Olcs\Form\Model\Form\Surrender;

use Zend\Form\Annotation as Form;

/**
 * @Form\Attributes({"id":"surrender-information-changed"})
 * @Form\Type("Common\Form\Form")
 */
class InformationChanged
{
    /**
     * @Form\Attributes({"type":"submit","class":"govuk-button govuk-button--start"})
     * @Form\Options({"label":"licence.surrender.information_changed.start_again"})
     * @Form\Type("\Zend\Form\Element\Button")
     */
    public $startAgain = null;

    /**
     * @Form\Attributes({"type":"submit","class":"action--primary large"})
     * @Form\Options({"label":"licence.surrender.information_changed.review_continue"})
     * @Form\Type("\Common\Form\Elements\InputFilters\ActionButton")
     */
    public $reviewAndContinue = null;
}
