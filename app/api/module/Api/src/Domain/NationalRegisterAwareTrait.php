<?php

namespace Dvsa\Olcs\Api\Domain;

/**
 * NationalRegisterAwareTrait
 */
trait NationalRegisterAwareTrait
{
    /**
     * @var array $nationalRegisterConfig
     */
    protected $nationalRegisterConfig;

    /**
     * @return array
     */
    public function getNationalRegisterConfig()
    {
        return $this->nationalRegisterConfig;
    }

    public function setNationalRegisterConfig(array $nationalRegisterConfig)
    {
        $this->nationalRegisterConfig = $nationalRegisterConfig;
    }
}
