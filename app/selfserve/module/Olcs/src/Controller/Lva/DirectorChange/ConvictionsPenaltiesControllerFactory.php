<?php

namespace Olcs\Controller\Lva\DirectorChange;

use Interop\Container\ContainerInterface;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @see ConvictionsPenaltiesController
 */
class ConvictionsPenaltiesControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ConvictionsPenaltiesController
     */
    public function createService(ServiceLocatorInterface $serviceLocator) : ConvictionsPenaltiesController
    {
        return $this->__invoke($serviceLocator, ConvictionsPenaltiesController::class);
    }

    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ConvictionsPenaltiesController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : ConvictionsPenaltiesController
    {
        $pluginManager = $container->get('ControllerPluginManager');
        assert($pluginManager instanceof ServiceLocatorInterface, 'Expected instance of ServiceLocatorInterface');
        return new ConvictionsPenaltiesController(
            $container->get(TranslatorInterface::class),
            $pluginManager->get('FlashMessenger')
        );
    }
}
