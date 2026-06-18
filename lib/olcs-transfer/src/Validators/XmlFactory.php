<?php

namespace Dvsa\Olcs\Transfer\Validators;

use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Xml\Security as XmlSecurityValidator;

class XmlFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Xml
    {
        $service = new Xml();
        $service->setSecurityValidator($container->get(XmlSecurityValidator::class));

        return $service;
    }
}
