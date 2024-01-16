<?php

namespace Olcs\Controller\Cases\Submission;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class RecommendationControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RecommendationController
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        assert($translationHelper instanceof TranslationHelperService);

        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        assert($flashMessenger instanceof FlashMessengerHelperService);

        $navigation = $container->get('Navigation');
        assert($navigation instanceof Navigation);

        return new RecommendationController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation
        );
    }
}
