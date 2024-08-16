<?php

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\DataHelperService;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Variation Financial History Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialHistoryController extends Lva\AbstractFinancialHistoryController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'external';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param DataHelperService $dataHelper
     * @param FileUploadHelperService $uploadHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        DataHelperService $dataHelper,
        FileUploadHelperService $uploadHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $dataHelper,
            $uploadHelper
        );
    }
}
