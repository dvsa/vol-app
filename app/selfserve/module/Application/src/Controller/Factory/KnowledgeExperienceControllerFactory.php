<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller\Factory;

use Common\Controller\Lva\Adapters\ApplicationKnowledgeExperienceAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Application\Controller\KnowledgeExperienceController;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Psr\Container\ContainerInterface;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\RestrictionHelperService;

final class KnowledgeExperienceControllerFactory implements FactoryInterface
{
    #[\Override]
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): KnowledgeExperienceController {
        return new KnowledgeExperienceController(
            $container->get(NiTextTranslation::class),
            $container->get(AuthorizationService::class),
            $container->get(FlashMessengerHelperService::class),
            $container->get(FormServiceManager::class),
            $container->get(ScriptFactory::class),
            $container->get(RestrictionHelperService::class),
            $container->get(StringHelperService::class),
            $container->get(ApplicationKnowledgeExperienceAdapter::class),
            $container->get(FileUploadHelperService::class)
        );
    }
}
