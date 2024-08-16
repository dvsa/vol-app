<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class NoOfPermitsMoroccoAnswerSaverFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return NoOfPermitsMoroccoAnswerSaver
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NoOfPermitsMoroccoAnswerSaver
    {
        return new NoOfPermitsMoroccoAnswerSaver(
            $container->get('QaGenericAnswerFetcher'),
            $container->get('PermitsBilateralCommonNoOfPermitsConditionalUpdater')
        );
    }
}
