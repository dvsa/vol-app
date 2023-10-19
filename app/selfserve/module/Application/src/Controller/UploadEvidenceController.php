<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\AbstractUploadEvidenceController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * External Upload Evidence Controller
 */
class UploadEvidenceController extends AbstractUploadEvidenceController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';

    protected RestrictionHelperService $restrictionHelper;
    protected StringHelperService $stringHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     * @param FileUploadHelperService $uploadHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        RestrictionHelperService $restrictionHelper,
        StringHelperService $stringHelper,
        FileUploadHelperService $uploadHelper
    ) {
        $this->restrictionHelper = $restrictionHelper;
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $uploadHelper
        );
    }
}
