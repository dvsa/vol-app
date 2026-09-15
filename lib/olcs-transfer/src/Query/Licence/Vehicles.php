<?php

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation\ContinueIfEmpty;

/**
 * @Transfer\RouteName("backend/licence/single/vehicles")
 */
class Vehicles extends AbstractQuery implements
    CacheableShortTermQueryInterface,
    PagedQueryInterface,
    OrderedQueryInterface,
    FiltersByIncludeActiveInterface
{
    use Identity;
    use PagedTrait;
    use OrderedTrait;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $includeRemoved;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean", options={"casting": false, "type": {
     *     Laminas\Filter\Boolean::TYPE_BOOLEAN,
     *     Laminas\Filter\Boolean::TYPE_INTEGER,
     *     Laminas\Filter\Boolean::TYPE_FALSE_STRING,
     *     Laminas\Filter\Boolean::TYPE_ZERO_STRING
     * }})
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {true, false}, "strict": true})
     * @Transfer\Escape(false)
     * @Transfer\ContinueIfEmpty(true)
     * @Transfer\Optional
     */
    protected $includeActive = true;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $vrm;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $disc;

    /**
     * @return mixed
     */
    public function getIncludeRemoved()
    {
        return $this->includeRemoved;
    }

    /**
     * @return mixed
     */
    #[\Override]
    public function getIncludeActive()
    {
        return $this->includeActive;
    }

    /**
     * @return mixed
     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * Get Disc Number
     *
     * @return string
     */
    public function getDisc()
    {
        return $this->disc;
    }
}
