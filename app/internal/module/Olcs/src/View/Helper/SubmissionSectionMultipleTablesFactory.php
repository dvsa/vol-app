<?php

namespace Olcs\View\Helper;

use Common\Service\Helper\TranslationHelperService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

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
        $translator = $container->get(TranslationHelperService::class);
        $service = new SubmissionSectionMultipleTables();
        $service->setTranslator($translator);
        return $service;
    }
}
