<?php

declare(strict_types=1);

namespace Olcs\Controller\Factory;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\ConversationsController;

class ConversationsControllerFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): ConversationsController {
        $niTextTranslationUtil = $container->get(NiTextTranslation::class);
        $authService = $container->get(AuthorizationService::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $formHelperService = $container->get(FormHelperService::class);

        return new ConversationsController(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $tableFactory,
            $formHelperService,
        );
    }
}
