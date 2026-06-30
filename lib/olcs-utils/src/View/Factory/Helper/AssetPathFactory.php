<?php

namespace Dvsa\Olcs\Utils\View\Factory\Helper;

use Dvsa\Olcs\Utils\View\Helper\AssetPath;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Factory for @see Dvsa\Olcs\Snapshot\View\Helper\AssetPath
 */
class AssetPathFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return AssetPath
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AssetPath
    {
        $config = $container->get('Config');
        return new AssetPath($config);
    }
}
