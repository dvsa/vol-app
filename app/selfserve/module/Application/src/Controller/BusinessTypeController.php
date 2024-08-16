<?php

/**
 * External Application Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\AbstractBusinessTypeController;
use Common\Controller\Lva\Adapters\GenericBusinessTypeAdapter;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Identity\IdentityProviderInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Application Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessTypeController extends AbstractBusinessTypeController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location  = 'external';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param IdentityProviderInterface $identityProvider
     * @param TranslationHelperService $translationHelper
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param QueryService $queryService
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     * @param GenericBusinessTypeAdapter $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        IdentityProviderInterface $identityProvider,
        TranslationHelperService $translationHelper,
        AnnotationBuilder $transferAnnotationBuilder,
        QueryService $queryService,
        protected RestrictionHelperService $restrictionHelper,
        protected StringHelperService $stringHelper,
        GenericBusinessTypeAdapter $lvaAdapter
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $scriptFactory,
            $identityProvider,
            $translationHelper,
            $transferAnnotationBuilder,
            $queryService,
            $lvaAdapter
        );
    }
}
