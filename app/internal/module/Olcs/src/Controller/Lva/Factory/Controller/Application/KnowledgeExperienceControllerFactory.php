<?php

declare(strict_types=1);

namespace Olcs\Controller\Lva\Factory\Controller\Application;

use Common\Controller\Lva\Adapters\ApplicationKnowledgeExperienceAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\ServiceManager\Factory\FactoryInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Lva\Application\KnowledgeExperienceController;
use Psr\Container\ContainerInterface;

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
            $container->get(FileUploadHelperService::class),
            $container->get('navigation')
        );
    }
}
