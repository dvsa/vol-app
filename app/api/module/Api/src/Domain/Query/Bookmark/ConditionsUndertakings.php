<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * ConditionsUndertakings
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakings extends AbstractQuery
{
    use Identity;

    protected $attachedTo;

    protected $conditionType;

    /**
     * @return mixed
     */
    public function getAttachedTo()
    {
        return $this->attachedTo;
    }

    /**
     * @return mixed
     */
    public function getConditionType()
    {
        return $this->conditionType;
    }
}
