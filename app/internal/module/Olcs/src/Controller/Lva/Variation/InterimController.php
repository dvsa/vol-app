<?php

/**
 * Internal Variation Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Olcs\Controller\Lva\Variation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Variation\UpdateInterim;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\VariationControllerInterface;
use Olcs\Controller\Lva\AbstractInterimController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Internal Variation Interim Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class InterimController extends AbstractInterimController implements VariationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';
    protected $updateInterimCommand = UpdateInterim::class;

    protected StringHelperService $stringHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormHelperService $formHelper
     * @param ScriptFactory $scriptFactory
     * @param TableFactory $tableFactory
     * @param StringHelperService $stringHelper
     * @param FormServiceManager $formServiceManager
     * @param $navigation
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormHelperService $formHelper,
        ScriptFactory $scriptFactory,
        TableFactory $tableFactory,
        StringHelperService $stringHelper,
        protected FormServiceManager $formServiceManager,
        protected $navigation
    ) {
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $formHelper,
            $scriptFactory,
            $tableFactory
        );
    }
}
