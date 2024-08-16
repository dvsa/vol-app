<?php

namespace Dvsa\Olcs\Api\Domain\Command\Publication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * Publish Licence
 */
final class Licence extends AbstractCommand
{
    use FieldType\Identity;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $publicationSection;

    /**
     * @return int
     */
    public function getPublicationSection()
    {
        return $this->publicationSection;
    }
}
