<?php

namespace Dvsa\Olcs\Api\Domain;

/**
 * Config Aware Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ConfigAwareTrait
{
    protected $config;

    public function setConfig(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }
}
