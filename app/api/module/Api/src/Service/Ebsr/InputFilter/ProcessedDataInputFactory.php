<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\InputFilter;

use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\BusRegNotFound;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\LocalAuthorityMissing;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\NewAppAlreadyExists;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\RegisteredBusRoute;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ProcessedData\VariationNumber;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class ProcessedDataInputFactory
 * @package Dvsa\Olcs\Api\Service\Ebsr\InputFilter
 */
class ProcessedDataInputFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Input
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Input
    {
        $inputName = 'processed_data';
        $service = new Input($inputName);
        $config = $container->get('config');
        $validatorChain = $service->getValidatorChain();
        //allows validators to be switched off (debug only, not to be used for production)
        if (!isset($config['ebsr']['validate'][$inputName]) || $config['ebsr']['validate'][$inputName] === true) {
            /** @var ServiceLocatorInterface $validatorManager */
            $validatorManager = $container->get('ValidatorManager');
            $validatorChain->attach($validatorManager->get(BusRegNotFound::class), true);
            $validatorChain->attach($validatorManager->get(NewAppAlreadyExists::class));
            $validatorChain->attach($validatorManager->get(RegisteredBusRoute::class));
            $validatorChain->attach($validatorManager->get(LocalAuthorityMissing::class));
            $validatorChain->attach($validatorManager->get(VariationNumber::class));
        }
        return $service;
    }
}
