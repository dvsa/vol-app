<?php

namespace Olcs\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SubmissionSectionMultipleTablesFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array|null $options
     * @return SubmissionSectionMultipleTables
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SubmissionSectionMultipleTables
    {
        $translator = $container->get('Translator');
        $service = new SubmissionSectionMultipleTables();
        $service->setTranslator($translator);
        return $service;
    }
}
