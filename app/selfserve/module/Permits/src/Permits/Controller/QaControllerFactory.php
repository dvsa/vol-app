<?php

namespace Permits\Controller;

use Common\Service\Helper\FileUploadHelperService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class QaControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param $requestedName
     * @param array $options
     * @return QaController
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) : QaController
    {
        return new QaController(
            $container->get('QaFormProvider'),
            $container->get('QaTemplateVarsGenerator'),
            $container->get('Helper\Translation'),
            $container->get('QaViewGeneratorProvider'),
            $container->get('QaApplicationStepsPostDataTransformer'),
            $container->get(FileUploadHelperService::class)
        );
    }
}
