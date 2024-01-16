<?php

namespace Admin\Controller;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\Placeholder;

class ContinuationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ContinuationController
    {
        $placeholder = $container->get('ViewHelperManager')->get(Placeholder::class);
        $flashMessengerHelper = $container->get(FlashMessengerHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $translationHelper = $container->get(TranslationHelperService::class);
        $tableFactory = $container->get(TableFactory::class);
        $formHelper = $container->get(FormHelperService::class);

        return new ContinuationController(
            $placeholder,
            $flashMessengerHelper,
            $scriptFactory,
            $translationHelper,
            $tableFactory,
            $formHelper
        );
    }
}
