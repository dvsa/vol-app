<?php

namespace Dvsa\Olcs\Api\Service\Nr\Filter;

use Laminas\Filter\AbstractFilter as AbstractFilter;
use Dvsa\Olcs\Transfer\Filter\Vrm as VrmFilter;

/**
 * Class Vrm
 * @package Dvsa\Olcs\Api\Service\Nr\Filter
 * @template-extends AbstractFilter<array>
 */
class Vrm extends AbstractFilter
{
    /**
     * @var VrmFilter
     */
    private $vrmFilter;

    /**
     * Gets the transfer vrm filter
     *
     * @return VrmFilter
     */
    public function getVrmFilter()
    {
        return $this->vrmFilter;
    }

    /**
     * Sets the transfer vrm filter
     *
     * @param VrmFilter $vrmFilter the transfer vrm filter
     *
     * @return void
     */
    public function setVrmFilter($vrmFilter)
    {
        $this->vrmFilter = $vrmFilter;
    }

    /**
     * Returns the result of filtering $value
     * 1. First of all will filter using the vrm filter from olcs-transfer
     * 2. Since we no longer validate the VRM is valid, we trim whatever we have to 15 characters, so we can ensure it
     * at least matches the size of the field in the DB
     *
     * @param array $value the input value
     *
     * @return array
     */
    public function filter($value)
    {
        $value['vrm'] = substr($this->getVrmFilter()->filter($value['vrm']), 0, 15);
        return $value;
    }
}
