<?php

namespace Admin\Controller;

use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\Placeholder;

class ContinuationChecklistReminderControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ContinuationChecklistReminderController
    {
        $placeholder = $container->get('ViewHelperManager')->get(Placeholder::class);

         $dateHelper = $container->get(DateHelperService::class);
         $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
         $scriptFactory = $container->get(ScriptFactory::class);
         $formHelper = $container->get(FormHelperService::class);
         $responseHelper = $container->get(ResponseHelperService::class);
         $tableFactory = $container->get(TableFactory::class);

        return new ContinuationChecklistReminderController(
            $placeholder,
            $dateHelper,
            $flashMessengerHelper,
            $scriptFactory,
            $formHelper,
            $responseHelper,
            $tableFactory
        );
    }
}
