<?php

namespace Dvsa\Olcs\Transfer\Query\Lva;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
abstract class AbstractGoodsVehicles extends AbstractQuery
{
    use Identity;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $vrm;

    /**
     * @var string|null
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $specified;

    /**
     * @var boolean
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $includeRemoved;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    protected $disc;

    /**
     * Get Vehicle vrm
     *
     * @return string
     */
    public function getVrm()
    {
        return $this->vrm;
    }

    /**
     * Get Specified (Yes|No)
     *
     * @return string|null
     */
    public function getSpecified(): ?string
    {
        return $this->specified;
    }

    /**
     * Get Should Removed be Included
     *
     * @return boolean
     */
    public function getIncludeRemoved()
    {
        return $this->includeRemoved;
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
