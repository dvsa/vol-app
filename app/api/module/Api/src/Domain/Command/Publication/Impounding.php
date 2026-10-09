<?php

/**
 * Publish an Impounding
 */

namespace Dvsa\Olcs\Api\Domain\Command\Publication;

use Dvsa\Olcs\Api\Domain\Command\AbstractIdOnlyCommand;
use Dvsa\Olcs\Api\Entity\Publication\Publication;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Publish an Impounding
 */
final class Impounding extends AbstractIdOnlyCommand
{
    use FieldType\ApplicationOptional;
    use FieldType\LicenceOptional;
    use FieldType\Pi;
    use FieldType\TrafficArea;

    #[Transfer\Filter("Laminas\Filter\StringTrim")]
    #[Transfer\Validator("Laminas\Validator\InArray", options: ["haystack" => ["All",Publication::PUB_TYPE_A_D,Publication::PUB_TYPE_N_P]])]
    protected $pubType;

    /**
     * @return string
     */
    public function getPubType()
    {
        return $this->pubType;
    }
}
