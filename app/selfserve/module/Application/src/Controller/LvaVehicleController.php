<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\AbstractGoodsVehiclesController;
use Common\Data\Mapper\Lva\GoodsVehiclesVehicle;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @deprecated Being replaced with new controllers
 */
class LvaVehicleController extends AbstractGoodsVehiclesController
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
     * @param TableFactory $tableFactory
     * @param GuidanceHelperService $guidanceHelper
     * @param TranslationHelperService $translationHelper
     * @param ScriptFactory $scriptFactory
     * @param VariationLvaService $variationLvaService
     * @param GoodsVehiclesVehicle $goodsVehiclesVehicleMapper
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        FormServiceManager $formServiceManager,
        TableFactory $tableFactory,
        GuidanceHelperService $guidanceHelper,
        TranslationHelperService $translationHelper,
        ScriptFactory $scriptFactory,
        VariationLvaService $variationLvaService,
        GoodsVehiclesVehicle $goodsVehiclesVehicleMapper,
        protected RestrictionHelperService $restrictionHelper,
        protected StringHelperService $stringHelper
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $formHelper,
            $flashMessengerHelper,
            $formServiceManager,
            $tableFactory,
            $guidanceHelper,
            $translationHelper,
            $scriptFactory,
            $variationLvaService,
            $goodsVehiclesVehicleMapper
        );
    }
}
