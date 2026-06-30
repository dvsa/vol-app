<?php

declare(strict_types=1);

namespace Common\FormService\Form\Continuation;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DeclarationFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Declaration
    {
        $translationHelper = $container->get(TranslationHelperService::class);
        $formHelper = $container->get(FormHelperService::class);
        $scriptFactory = $container->get(ScriptFactory::class);
        $urlHelper = $container->get(UrlHelperService::class);
        return new Declaration($formHelper, $translationHelper, $scriptFactory, $urlHelper);
    }
}
