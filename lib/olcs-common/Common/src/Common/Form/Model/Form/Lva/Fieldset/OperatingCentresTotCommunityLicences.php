<?php

declare(strict_types=1);

namespace Common\Form\Model\Form\Lva\Fieldset;

use Laminas\Form\Annotation as Form;

/**
 * @Form\Options({})
 */
class OperatingCentresTotCommunityLicences
{
    /**
     * @Form\Attributes({"class":"short","id":"totCommunityLicences"})
     * @Form\Options({
     *     "label": "application_operating-centres_authorisation.data.totCommunityLicences",
     *     "hint": "application_operating-centres_authorisation.data.totCommunityLicences.hint"
     * })
     * @Form\Validator("Digits")
     * @Form\Validator("Between", options={"min":0, "max": 10000})
     * @Form\Filter("\Laminas\Filter\ToNull", options={"type":"string"})
     */
    public $totCommunityLicences;
}
