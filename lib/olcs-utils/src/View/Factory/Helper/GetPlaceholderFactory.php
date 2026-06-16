<?php

namespace Dvsa\Olcs\Utils\View\Factory\Helper;

use Dvsa\Olcs\Utils\View\Helper\GetPlaceholder;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GetPlaceholderFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): \Closure
    {
        $viewHelperManager = $container->get('ViewHelperManager');
        $placeholder = $viewHelperManager->get('placeholder');

        return fn($name) => new GetPlaceholder($placeholder->__invoke($name));
    }
}
