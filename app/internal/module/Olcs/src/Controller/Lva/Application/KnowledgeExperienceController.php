<?php

declare(strict_types=1);

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractKnowledgeExperienceController;
use Common\Controller\Lva\Adapters\ApplicationKnowledgeExperienceAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

final class KnowledgeExperienceController extends AbstractKnowledgeExperienceController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';

    protected string $location = 'internal';

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        protected RestrictionHelperService $restrictionHelper,
        protected StringHelperService $stringHelper,
        ApplicationKnowledgeExperienceAdapter $lvaAdapter,
        FileUploadHelperService $uploadHelper,
        protected $navigation
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $lvaAdapter,
            $uploadHelper
        );
    }
}
