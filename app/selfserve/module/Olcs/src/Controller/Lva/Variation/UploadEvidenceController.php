<?php

namespace Olcs\Controller\Lva\Variation;

use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\AbstractUploadEvidenceController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Upload Evidence Controller
 */
class UploadEvidenceController extends AbstractUploadEvidenceController
{
    use VariationControllerTrait;

    protected $lva = 'variation';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FileUploadHelperService $uploadHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $uploadHelper
        );
    }
}
