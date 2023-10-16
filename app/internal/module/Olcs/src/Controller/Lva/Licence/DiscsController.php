<?php

/**
 * Internal Licence Discs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * Internal Licence Discs Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DiscsController extends Lva\AbstractDiscsController implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'internal';

    /**
     * @param NiTextTranslation           $niTextTranslationUtil
     * @param AuthorizationService        $authService
     * @param FormHelperService           $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager          $formServiceManager
     * @param TableFactory                $tableFactory
     * @param GuidanceHelperService       $guidanceHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        TableFactory $tableFactory,
        GuidanceHelperService $guidanceHelper
    ) {
        $this->formHelper = $formHelper;
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->formServiceManager = $formServiceManager;
        $this->tableFactory = $tableFactory;
        $this->guidanceHelper = $guidanceHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }
}
