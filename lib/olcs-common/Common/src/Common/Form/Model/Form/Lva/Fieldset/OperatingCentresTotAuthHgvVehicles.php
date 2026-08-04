<?php

declare(strict_types=1);

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({})
 */
class OperatingCentresTotAuthHgvVehicles
{
    /**
     * @Form\Required(true)
     * @Form\Type("Text")
     * @Form\Attributes({"class":"short","id":"totAuthHgvVehicles","required":false,"pattern":"\d*"})
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation.data.totAuthHgvVehicles.hgvs-label",
     *     "hint-below-class": "govuk-hint govuk-body govuk-!-font-size-16 govuk-!-margin-top-2"
     * })
     * @Form\Validator("Digits", options={"break_chain_on_failure": true})
     * @Form\Validator("Between", options={"min":1, "max": 5000})
     * @Form\Filter("\Laminas\Filter\ToNull", options={"type":"string"})
     */
    public $totAuthHgvVehicles;
}
