<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

class VehiclesDeclarationsController extends Lva\AbstractVehiclesDeclarationsController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location  = 'external';

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        protected RestrictionHelperService $restrictionHelper,
        protected StringHelperService $stringHelper,
        protected FileUploadHelperService $uploadHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $uploadHelper,
            $flashMessengerHelper,
        );
    }
}
