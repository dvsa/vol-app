<?php

namespace Olcs\Controller\Factory\Entity;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Entity\ViewController;
use LmcRbacMvc\Service\AuthorizationService;

class ViewControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return ViewController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ViewController
    {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);

        return new ViewController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $tableFactory
        );
    }
}
