<?php

/**
 * Publish Application
 */

namespace Dvsa\Olcs\Transfer\Command\Publication;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * @Transfer\RouteName("backend/publication/application")
 * @Transfer\Method("POST")
 */
final class Application extends AbstractCommand
{
    use FieldType\Identity;
    use FieldType\TrafficArea;

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
