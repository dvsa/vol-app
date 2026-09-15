<?php

namespace Dvsa\Olcs\Transfer\Query\Letter\PreviewRecord;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * Resolve a caseworker-supplied search term to a licence and its applications, for
 * choosing the record a letter preview renders against.
 *
 * The term is a licence number or a database id -- caseworkers think in licence
 * numbers, so the lookup accepts both and disambiguates by shape.
 *
 * @Transfer\RouteName("backend/letter/preview-record/lookup")
 */
final class Lookup extends AbstractQuery
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Filter("Laminas\Filter\StringToUpper")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1, "max":18})
     */
    protected $term;

    /**
     * @return string
     */
    public function getTerm()
    {
        return $this->term;
    }
}
