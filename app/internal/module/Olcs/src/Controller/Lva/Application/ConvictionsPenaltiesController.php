<?php

/**
 * Internal Application Convictions and penalties Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * Internal Application Convictions and penalties Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConvictionsPenaltiesController extends Lva\AbstractConvictionsPenaltiesController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected RestrictionHelperService $restrictionHelper;

    /**
     * @param NiTextTranslation           $niTextTranslationUtil
     * @param AuthorizationService        $authService
     * @param FormHelperService           $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager          $formServiceManager
     * @param TableFactory                $tableFactory
     * @param StringHelperService         $stringHelper
     * @param RestrictionHelperService    $restrictionHelper
     * @param ScriptFactory               $scriptFactory
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        TableFactory $tableFactory,
        StringHelperService $stringHelper,
        RestrictionHelperService $restrictionHelper,
        ScriptFactory $scriptFactory
    ) {
        $this->stringHelper = $stringHelper;
        $this->restrictionHelper = $restrictionHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $scriptFactory
        );
    }
}
