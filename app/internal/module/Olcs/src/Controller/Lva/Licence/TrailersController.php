<?php

/**
 * Class TrailersController
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Query\QuerySender;
use Common\Service\Helper\DateHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class TrailersController
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TrailersController extends Lva\AbstractTrailersController implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'internal';
    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param TableFactory $tableFactory
     * @param ScriptFactory $scriptFactory
     * @param DateHelperService $dateHelper
     * @param QuerySender $querySender
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        TableFactory $tableFactory,
        ScriptFactory $scriptFactory,
        DateHelperService $dateHelper,
        QuerySender $querySender,
        $navigation
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $tableFactory,
            $scriptFactory,
            $dateHelper,
            $querySender
        );
        $this->navigation = $navigation;
    }
}
