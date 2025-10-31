<?php

declare(strict_types=1);

namespace Olcs\Controller\Factory\Letter;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Olcs\Controller\Letter\LetterGenerationController;
use Psr\Container\ContainerInterface;

/**
 * Factory for LetterGenerationController
 */
class LetterGenerationControllerFactory implements FactoryInterface
{
    /**
     * Invoke
     *
     * @param ContainerInterface $container Container
     * @param string $requestedName Requested name
     * @param array|null $options Options
     * @return LetterGenerationController
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        array $options = null
    ): LetterGenerationController {
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $flashMessenger = $container->get(FlashMessengerHelperService::class);
        $navigation = $container->get('navigation');

        return new LetterGenerationController(
            $translationHelper,
            $formHelper,
            $flashMessenger,
            $navigation
        );
    }
}
