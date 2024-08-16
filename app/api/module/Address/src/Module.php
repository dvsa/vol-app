<?php

namespace Dvsa\Olcs\Address;

use Dvsa\Olcs\Address\Service\Address;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;

class Module
{
    public function onBootstrap($e): void
    {
        $this->overwriteAddressServiceIfToggleDisabled($e);
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Empty on purpose to defer loading to composer
     */
    public function getAutoloaderConfig()
    {
    }

    private function overwriteAddressServiceIfToggleDisabled($e): void
    {
        $toggleService = $e->getApplication()->getServiceManager()->get(ToggleService::class);

        // Overwrite the DvsaAddressService alias with the legacy AddressService if toggle is disabled
        if ($toggleService->isDisabled(FeatureToggle::USE_NEW_ADDRESS_SERVICE)) {
            $serviceManager = $e->getApplication()->getServiceManager();
            $serviceManager->setAlias('AddressService', Address::class);
        }
    }
}
