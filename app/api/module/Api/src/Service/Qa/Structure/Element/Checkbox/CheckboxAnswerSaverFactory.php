<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Checkbox;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class CheckboxAnswerSaverFactory implements FactoryInterface
{
    /**
     * invoke method
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return CheckboxAnswerSaver
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): CheckboxAnswerSaver
    {
        return new CheckboxAnswerSaver(
            $container->get('QaGenericAnswerWriter'),
            $container->get('QaGenericAnswerFetcher')
        );
    }
}
