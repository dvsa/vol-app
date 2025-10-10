<?php

declare(strict_types=1);

namespace Admin\Controller\Letter;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class LetterIssueControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LetterIssueController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        $navigation = $container->get('navigation');
        $tableFactory = $container->get(TableFactory::class);

        return new LetterIssueController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation,
            $tableFactory
        );
    }
}