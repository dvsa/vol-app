<?php

declare(strict_types=1);

namespace Common\FormService\Form\Continuation;

use Common\Service\Helper\FormHelperService;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ConditionsUndertakingsFactory implements FactoryInterface
{
    /**
     * @param $requestedName
     * @param array|null $options
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ConditionsUndertakings
    {
        $formHelper = $container->get(FormHelperService::class);
        return new ConditionsUndertakings($formHelper);
    }
}
