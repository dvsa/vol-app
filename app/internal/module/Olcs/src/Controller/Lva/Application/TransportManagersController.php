<?php

/**
 * Internal Application Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Common\Controller\Lva\Adapters\ApplicationTransportManagerAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TransportManagerHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Application Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagersController extends Lva\AbstractTransportManagersController implements
    ApplicationControllerInterface,
    TransportManagerControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location = 'internal';

    protected StringHelperService $stringHelper;
    protected RestrictionHelperService $restrictionHelper;

    protected $navigation;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param ScriptFactory $scriptFactory
     * @param QueryService $queryService
     * @param CommandService $commandService
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param TransportManagerHelperService $transportManagerHelper
     * @param StringHelperService $stringHelper
     * @param ApplicationTransportManagerAdapter $lvaAdapter
     * @param RestrictionHelperService $restrictionHelper
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        QueryService $queryService,
        CommandService $commandService,
        AnnotationBuilder $transferAnnotationBuilder,
        TransportManagerHelperService $transportManagerHelper,
        StringHelperService $stringHelper,
        ApplicationTransportManagerAdapter $lvaAdapter,
        RestrictionHelperService $restrictionHelper,
        $navigation
    ) {
        $this->stringHelper = $stringHelper;
        $this->restrictionHelper = $restrictionHelper;
        $this->navigation = $navigation;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $flashMessengerHelper,
            $scriptFactory,
            $queryService,
            $commandService,
            $transferAnnotationBuilder,
            $transportManagerHelper,
            $lvaAdapter
        );
    }
}
