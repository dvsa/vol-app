<?php

declare(strict_types=1);

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({})
 */
class OperatingCentresTotAuthTrailers
{
    /**
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"short","id":"totAuthTrailers","required":false,"pattern":"\d*"})
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation.data.totAuthTrailers",
     *     "hint-below-class": "govuk-hint govuk-body govuk-!-font-size-16 govuk-!-margin-top-2"
     * })
     * @Form\Validator("Digits")
     * @Form\Validator("Between", options={"min":0, "max": 5000})
     * @Form\Filter("\Laminas\Filter\ToNull", options={"type":"string"})
     */
    public $totAuthTrailers;
}
