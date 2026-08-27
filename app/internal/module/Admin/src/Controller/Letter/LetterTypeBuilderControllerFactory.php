<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LetterTypeBuilderControllerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): LetterTypeBuilderController
    {
        return new LetterTypeBuilderController(
            $container->get(TranslationHelperService::class),
            $container->get(FormHelperService::class),
            $container->get(FlashMessengerHelperService::class),
            $container->get('navigation'),
            $container->get(TableFactory::class)
        );
    }
}
