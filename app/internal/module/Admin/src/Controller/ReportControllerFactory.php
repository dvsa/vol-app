<?php

namespace Admin\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Helper\Placeholder;

class ReportControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ReportController
    {
        $scriptFactory = $container->get(ScriptFactory::class);
        $tableFactory = $container->get(TableFactory::class);
        $formHelper = $container->get(FormHelperService::class);
        $placeholder = $container->get('ViewHelperManager')->get(Placeholder::class);

        return new ReportController(
            $scriptFactory,
            $tableFactory,
            $formHelper,
            $placeholder
        );
    }
}
