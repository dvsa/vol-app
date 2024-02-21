<?php

namespace Olcs\Controller\TransportManager\Details;

use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Psr\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TransportManagerDetailsCompetenceControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TransportManagerDetailsCompetenceController
    {
        $translationHelperService = $container->get(TranslationHelperService::class);
        assert($translationHelperService instanceof TranslationHelperService);

        $formHelper = $container->get(FormHelperService::class);
        assert($formHelper instanceof FormHelperService);

        $flashMessengerHelperService = $container->get(FlashMessengerHelperService::class);
        assert($flashMessengerHelperService instanceof FlashMessengerHelperService);

        $navigation = $container->get('navigation');
        assert($navigation instanceof Navigation);

        $transferAnnotationBuilder = $container->get(TransferAnnotationBuilder::class);
        assert($transferAnnotationBuilder instanceof TransferAnnotationBuilder);

        $queryService = $container->get(QueryService::class);
        assert($queryService instanceof QueryService);

        $transportMangerHelper = $container->get(TransportManagerHelperService::class);
        assert($transportMangerHelper instanceof TransportManagerHelperService);

        $uploadHelper = $container->get(FileUploadHelperService::class);

        return new TransportManagerDetailsCompetenceController(
            $translationHelperService,
            $formHelper,
            $flashMessengerHelperService,
            $navigation,
            $transferAnnotationBuilder,
            $queryService,
            $transportMangerHelper,
            $uploadHelper
        );
    }
}
