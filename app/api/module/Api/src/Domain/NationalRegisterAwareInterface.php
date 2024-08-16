<?php

namespace Dvsa\Olcs\Api\Domain;

/**
 * NationalRegisterAwareInterface
 */
interface NationalRegisterAwareInterface
{
    public function setNationalRegisterConfig(array $nationalRegisterConfig);

    /**
     * @return array national register configuration
     */
    public function getNationalRegisterConfig();
}
