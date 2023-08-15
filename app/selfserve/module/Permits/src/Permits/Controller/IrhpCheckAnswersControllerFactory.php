<?php

declare(strict_types=1);

namespace Permits\Controller;

use Common\Preference\Language;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Permits\Data\Mapper\MapperManager;

class IrhpCheckAnswersControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return IrhpCheckAnswersController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): IrhpCheckAnswersController
    {
        if (method_exists($container, 'getServiceLocator') && $container->getServiceLocator()) {
            $container = $container->getServiceLocator();
        }
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $tableBuilder = $container->get(TableFactory::class);
        $mapperManager = $container->get(MapperManager::class);
        $languagePreference = $container->get(Language::class);
        return new IrhpCheckAnswersController($translationHelper, $formHelper, $tableBuilder, $mapperManager, $languagePreference);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return IrhpCheckAnswersController
     * @deprecated
     */
    public function createService(ServiceLocatorInterface $serviceLocator): IrhpCheckAnswersController
    {
        return $this->__invoke($serviceLocator, IrhpCheckAnswersController::class);
    }
}
