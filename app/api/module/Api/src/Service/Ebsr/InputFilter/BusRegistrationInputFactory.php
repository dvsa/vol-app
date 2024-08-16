<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\InputFilter;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\ExistingRegNo;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Subsidy;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Via;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectIsTxcApp;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectNaptanCodes;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectReceivedDate;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\NoticePeriod;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ApplicationType;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EffectiveDate;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\Licence;
use Olcs\XmlTools\Filter\MapXmlFile;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\MiscSnJustification;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ServiceNo;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\EndDate;
use Psr\Container\ContainerInterface;

/**
 * Class BusRegistrationInputFactory
 * @package Dvsa\Olcs\Api\Service\Ebsr\InputFilter
 */
class BusRegistrationInputFactory implements FactoryInterface
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
        $inputName = 'bus_registration';
        $service = new Input($inputName);
        $config = $container->get('config');
        /** @var ContainerInterface $filterManager */
        $filterManager = $container->get('FilterManager');
        /** @var MapXmlFile $mapXmlFile */
        $mapXmlFile = $filterManager->get(MapXmlFile::class);
        $mapXmlFile->setMapping($container->get('TransExchangeXmlMapping'));
        $filterChain = $service->getFilterChain();
        $filterChain->attach($mapXmlFile);
        $filterChain->attach($filterManager->get(InjectIsTxcApp::class));
        $filterChain->attach($filterManager->get(InjectReceivedDate::class));
        $filterChain->attach($filterManager->get(InjectNaptanCodes::class));
        $filterChain->attach($filterManager->get(NoticePeriod::class));
        $filterChain->attach($filterManager->get(Subsidy::class));
        $filterChain->attach($filterManager->get(Via::class));
        $filterChain->attach($filterManager->get(ExistingRegNo::class));
        $filterChain->attach($filterManager->get(MiscSnJustification::class));
        $validatorChain = $service->getValidatorChain();
        //allows validators to be switched off (debug only, not to be used for production)
        if (!isset($config['ebsr']['validate'][$inputName]) || $config['ebsr']['validate'][$inputName] === true) {
            /** @var ContainerInterface $validatorManager */
            $validatorManager = $container->get('ValidatorManager');
            $validatorChain->attach($validatorManager->get(EffectiveDate::class));
            $validatorChain->attach($validatorManager->get(ApplicationType::class));
            $validatorChain->attach($validatorManager->get(Licence::class));
            $validatorChain->attach($validatorManager->get(ServiceNo::class));
            $validatorChain->attach($validatorManager->get(EndDate::class));
        }
        return $service;
    }
}
