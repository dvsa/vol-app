<?php

namespace Olcs\Controller\Lva\DirectorChange;

use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @see LicenceHistoryController
 */
class LicenceHistoryControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return LicenceHistoryController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $pluginManager = $serviceLocator->get('ControllerPluginManager');
        assert($pluginManager instanceof ServiceLocatorInterface, 'Expected instance of ServiceLocatorInterface');
        return new LicenceHistoryController(
            $serviceLocator->get(TranslatorInterface::class),
            $pluginManager->get('FlashMessenger')
        );
    }
}
