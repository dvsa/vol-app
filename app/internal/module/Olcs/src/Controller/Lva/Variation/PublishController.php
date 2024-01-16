<?php

namespace Olcs\Controller\Lva\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

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

    protected FlashMessengerHelperService $flashMessengerHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param StringHelperService $stringHelper
     * @param FormServiceManager $formServiceManager
     * @param $navigation
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        StringHelperService $stringHelper,
        FormServiceManager $formServiceManager,
        $navigation,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        $this->stringHelper = $stringHelper;
        $this->formServiceManager = $formServiceManager;
        $this->navigation = $navigation;
        $this->flashMessengerHelper = $flashMessengerHelper;

        parent::__construct($niTextTranslationUtil, $authService, $formHelper);
    }
}
