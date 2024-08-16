<?php

namespace Olcs\Controller\Lva\Application;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Application PublishController
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PublishController extends \Olcs\Controller\Lva\AbstractPublishController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param StringHelperService $stringHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        StringHelperService $stringHelper,
        protected RestrictionHelperService $restrictionHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected $navigation
    ) {
        $this->stringHelper = $stringHelper;

        parent::__construct($niTextTranslationUtil, $authService, $formHelper);
    }
}
