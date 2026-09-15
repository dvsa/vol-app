<?php

/**
 * Get a list of Licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/licence")
 */
final class GetList extends AbstractQuery implements \Dvsa\Olcs\Transfer\Query\OrderedQueryInterface
{
    use \Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $organisation;

    /**
     * @Transfer\Optional
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\LicenceStatus")
     */
    protected $excludeStatuses = [];

    /**
     * Get a Organsation ID
     *
     * @return int
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function getExcludeStatuses()
    {
        return $this->excludeStatuses;
    }
}
