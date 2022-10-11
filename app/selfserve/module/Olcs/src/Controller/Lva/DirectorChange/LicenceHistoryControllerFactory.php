<?php

namespace Olcs\Controller\Lva\DirectorChange;

use Interop\Container\ContainerInterface;
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
    public function createService(ServiceLocatorInterface $serviceLocator) : LicenceHistoryController
    {
        return $this->__invoke($serviceLocator, LicenceHistoryController::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return LicenceHistoryController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : LicenceHistoryController
    {
        $pluginManager = $container->get('ControllerPluginManager');
        assert($pluginManager instanceof ServiceLocatorInterface, 'Expected instance of ServiceLocatorInterface');
        return new LicenceHistoryController(
            $container->get(TranslatorInterface::class),
            $pluginManager->get('FlashMessenger')
        );
    }
}
