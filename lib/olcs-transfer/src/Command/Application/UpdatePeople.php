<?php

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractPeople;
use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/application/single/people/person")
 * @Transfer\Method("PUT")
 */
final class UpdatePeople extends AbstractPeople
{
    use Traits\Version;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $person;

    /**
     * Get Person Id
     *
     * @return int
     */
    public function getPerson()
    {
        return $this->person;
    }
}
