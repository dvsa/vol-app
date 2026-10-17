<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Query\LongText;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Fetch a block of Long Text by the key application code addresses it with.
 *
 * @Transfer\RouteName("backend/long-text/by-reference-key")
 */
final class ByReferenceKey extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\Regex", options={"pattern":"/^[a-z0-9]+(-[a-z0-9]+)*$/"})
     */
    protected $referenceKey;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $locale;

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getReferenceKey(): ?string
    {
        return $this->referenceKey;
    }
}
