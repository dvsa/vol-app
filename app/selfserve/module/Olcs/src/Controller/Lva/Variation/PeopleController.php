<?php

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Adapters\VariationPeopleAdapter;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * External Variation People Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends Lva\AbstractPeopleController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'external';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param FormServiceManager $formServiceManager
     * @param ScriptFactory $scriptFactory
     * @param VariationLvaService $variationLvaService
     * @param GuidanceHelperService $guidanceHelper
     * @param VariationPeopleAdapter $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FormServiceManager $formServiceManager,
        ScriptFactory $scriptFactory,
        VariationLvaService $variationLvaService,
        GuidanceHelperService $guidanceHelper,
        VariationPeopleAdapter $lvaAdapter
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $formServiceManager,
            $scriptFactory,
            $variationLvaService,
            $guidanceHelper,
            $lvaAdapter
        );
    }
}
