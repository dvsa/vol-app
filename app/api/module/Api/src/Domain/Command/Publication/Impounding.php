<?php

/**
 * Publish an Impounding
 */

namespace Dvsa\Olcs\Api\Domain\Command\Publication;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * Publish an Impounding
 */
final class Impounding extends AbstractIdOnlyCommand
{
    use FieldType\ApplicationOptional;
    use FieldType\LicenceOptional;
    use FieldType\Pi;
    use FieldType\TrafficArea;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"All","A&D","N&P"}})
     */
    protected $pubType;

    /**
     * @return string
     */
    public function getPubType()
    {
        return $this->pubType;
    }
}
