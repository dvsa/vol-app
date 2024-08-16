<?php

namespace Dvsa\Olcs\Api\Service\Ebsr\InputFilter;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Dvsa\Olcs\Api\Service\InputFilter\Input;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingSection;
use Dvsa\Olcs\Api\Service\Ebsr\RulesValidator\ShortNotice\MissingReason;
use Psr\Container\ContainerInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ShortNoticeInputFactory
 * @package Dvsa\Olcs\Api\Service\Ebsr\InputFilter
 */
class ShortNoticeInputFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return Input
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Input
    {
        $inputName = 'short_notice';
        $service = new Input($inputName);
        $config = $container->get('config');
        $validatorChain = $service->getValidatorChain();
        //allows validators to be switched off (debug only, not to be used for production)
        if (!isset($config['ebsr']['validate'][$inputName]) || $config['ebsr']['validate'][$inputName] === true) {
            /** @var ServiceLocatorInterface $validatorManager */
            $validatorManager = $container->get('ValidatorManager');
            $validatorChain->attach($validatorManager->get(MissingSection::class), true);
            $validatorChain->attach($validatorManager->get(MissingReason::class));
        }
        return $service;
    }
}
