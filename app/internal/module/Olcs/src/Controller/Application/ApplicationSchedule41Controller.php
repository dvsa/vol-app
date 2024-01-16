<?php

/**
 * ApplicationSchedule41Controller.php
 */

namespace Olcs\Controller\Application;

use Common\Controller\Lva\Schedule41Controller;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class ApplicationSchedule41Controller
 *
 * Application41 schedule controller.
 *
 * @package Olcs\Controller\Application
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class ApplicationSchedule41Controller extends Schedule41Controller implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected $section = 'operating_centres';

    protected StringHelperService $stringHelper;

    protected $navigation;

    protected RestrictionHelperService $restrictionHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        FlashMessengerHelperService $flashMessengerHelper,
        StringHelperService $stringHelper,
        $navigation,
        RestrictionHelperService $restrictionHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService, $formHelper, $tableFactory, $flashMessengerHelper);
        $this->stringHelper = $stringHelper;
        $this->navigation = $navigation;
        $this->restrictionHelper = $restrictionHelper;
    }
}
