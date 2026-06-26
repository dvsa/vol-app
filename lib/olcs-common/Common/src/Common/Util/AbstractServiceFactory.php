<?php

namespace Common\Util;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return ($this->getClassName($requestedName) !== false);
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $serviceClassName = $this->getClassName($requestedName);

        if (!$serviceClassName) {
            throw new \Exception('Service not found: ' . $requestedName);
        }

        $service = new $serviceClassName();

        if ($service instanceof FactoryInterface) {
            return $service->__invoke($container, $requestedName, $options);
        }

        return $service;
    }

    /**
     * Get the class name from the service name
     *
     * Helper\Access becomes Common\Service\Helper\AccessHelperService
     * Access becomes Common\Service\Access
     *
     * @param string $name Class name
     *
     * @return class-string|false
     */
    protected function getClassName($name): string|false
    {
        $namespaces = [
            'Olcs\Service\\',
            'Admin\Service\\',
            'Common\Service\\'
        ];

        if (strstr($name, '\\')) {
            [$type, $name] = explode('\\', $name, 2);

            foreach ($namespaces as $namespace) {
                $className = $namespace . $type . '\\' . $name . $type . 'Service';
                if (class_exists($className)) {
                    return $className;
                }
            }
        }

        return false;
    }
}
