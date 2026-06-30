<?php

declare(strict_types=1);

namespace Common\Controller\Factory\FeatureToggle;

use Common\Service\Cqrs\Query\QuerySender;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

/**
 * A factory that enables developers to create a controller in two different ways depending on whether a  feature toggle
 * has been enabled or disabled.
 *
 * @see \CommonTest\Controller\Factory\FeatureToggle\BinaryFeatureToggleAwareControllerFactoryTest
 */
abstract class BinaryFeatureToggleAwareControllerFactory implements FactoryInterface
{
    /**
     * @return string[]
     */
    abstract protected function getFeatureToggleNames(): array;

    /**
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    abstract protected function createServiceWhenEnabled(ContainerInterface $container, $requestedName, array $options = null);

    /**
     * @param $requestedName
     * @param array|null $options
     * @return mixed
     */
    abstract protected function createServiceWhenDisabled(ContainerInterface $container, $requestedName, array $options = null);

    /**
     * @param mixed $requestedName
     * @param array|null $options
     * @return mixed
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($this->featureTogglesAreEnabled($container, $this->getFeatureToggleNames())) {
            return $this->createServiceWhenEnabled($container, $requestedName, $options);
        }

        return $this->createServiceWhenDisabled($container, $requestedName, $options);
    }

    /**
     * @param string[] $featureToggles
     */
    protected function featureTogglesAreEnabled(ContainerInterface $container, array $featureToggles): bool
    {
        if ($featureToggles === []) {
            return true;
        }

        $querySender = $container->get('QuerySender');

        assert($querySender instanceof QuerySender, 'Expected instance of QuerySender');
        return $querySender->featuresEnabled($this->getFeatureToggleNames());
    }
}
