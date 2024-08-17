<?php

namespace Olcs\Form\Validator\Factory;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Olcs\Form\Validator\UniqueConsultantDetails;
use Olcs\Session\ConsultantRegistration;

class UniqueConsultantDetailsFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $session = $container->get(ConsultantRegistration::class);
        return new UniqueConsultantDetails($session, $options);
    }
}
