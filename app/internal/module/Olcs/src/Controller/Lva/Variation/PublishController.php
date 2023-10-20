<?php

namespace Olcs\Controller\Lva\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * Variation PublishController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PublishController extends \Olcs\Controller\Lva\AbstractPublishController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected FormServiceManager $formServiceManager;
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param StringHelperService $stringHelper
     * @param FormServiceManager $formServiceManager
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        StringHelperService $stringHelper,
        FormServiceManager $formServiceManager,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->formServiceManager = $formServiceManager;
        $this->navigation = $navigation;

        parent::__construct($niTextTranslationUtil, $authService, $formHelper);
    }
}
