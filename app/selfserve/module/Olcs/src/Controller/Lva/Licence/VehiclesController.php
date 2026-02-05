<?php

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractGoodsVehiclesController;
use Common\Controller\Lva\Adapters\LicenceLvaAdapter;
use Common\Data\Mapper\Lva\GoodsVehiclesVehicle;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\ResponseHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Lva\VariationLvaService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @see VehicleControllerFactory
 */
class VehiclesController extends AbstractGoodsVehiclesController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'external';

    protected static $exportDataMap = [
        'licence' => Query\Licence\GoodsVehiclesExport::class,
        'variation' => Query\Variation\GoodsVehiclesExport::class,
        'application' => Query\Application\GoodsVehiclesExport::class,
    ];

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
     * @param LicenceLvaAdapter $lvaAdapter
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
        protected ResponseHelperService $responseHelper,
        protected LicenceLvaAdapter $lvaAdapter
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

    #[\Override]
    protected function getScripts(): array
    {
        $scripts  = parent::getScripts();
        $scripts[] = 'vehicles';
        return $scripts;
    }

    /**
     * Specific functionality for CRUD actions
     *
     * @param string $action Crud Action
     *
     * @return \Laminas\Http\Response|null
     */
    protected function checkForAlternativeCrudAction($action)
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        if ($action === 'export') {
            // reset page and limit
            $query = $request->getPost('query');
            unset(
                $query['page'],
                $query['limit']
            );
            $request->getPost()->set('query', $query);

            return $this->responseHelper
                ->tableToCsv(
                    $this->getResponse(),
                    $this->getTable($this->getExportData(), $this->getFilters()),
                    'vehicles'
                );
        }

        return null;
    }

    /**
     * Request vehicle data for export
     *
     * @return array
     */
    private function getExportData()
    {
        $dtoData = $this->getFilters();
        $dtoData['id'] = $this->getIdentifier();

        $dtoClass = self::$exportDataMap[$this->lva];

        $response = $this->handleQuery($dtoClass::create($dtoData));

        return [
            'licenceVehicles' => $response->getResult(),
        ];
    }
}
