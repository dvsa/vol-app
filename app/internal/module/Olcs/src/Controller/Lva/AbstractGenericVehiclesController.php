<?php

/**
 * Abstract Generic Vehicles Goods Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractGoodsVehiclesController;
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
use Dvsa\Olcs\Transfer\Command\Application\CreateVehicleListDocument as ApplicationCreateDocument;
use Dvsa\Olcs\Transfer\Command\Licence\CreateVehicleListDocument as LicenceCreateDocument;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Abstract Generic Vehicles Goods Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractGenericVehiclesController extends AbstractGoodsVehiclesController
{
    protected $docGenerationMap = [
        'licence' => LicenceCreateDocument::class,
        'variation' => ApplicationCreateDocument::class,
        'application' => ApplicationCreateDocument::class
    ];

    protected static $exportDataMap = [
        'licence' => Query\Licence\GoodsVehiclesExport::class,
        'variation' => Query\Variation\GoodsVehiclesExport::class,
        'application' => Query\Application\GoodsVehiclesExport::class,
    ];

    /**
     * @param NiTextTranslation           $niTextTranslationUtil
     * @param AuthorizationService        $authService
     * @param FormHelperService           $formHelper
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormServiceManager          $formServiceManager
     * @param TableFactory                $tableFactory
     * @param GuidanceHelperService       $guidanceHelper
     * @param TranslationHelperService    $translationHelper
     * @param ScriptFactory               $scriptFactory
     * @param VariationLvaService         $variationLvaService
     * @param GoodsVehiclesVehicle        $goodsVehiclesVehicleMapper
     * @param ResponseHelperService       $responseHelper
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
        protected ResponseHelperService $responseHelper
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

    /**
     * Print vehicles action
     *
     * @return \Laminas\Http\Response
     */
    public function printVehiclesAction()
    {
        $dtoClass = $this->docGenerationMap[$this->lva];
        $response = $this->handleCommand($dtoClass::create(['id' => $this->getIdentifier()]));

        $fm = $this->flashMessengerHelper;

        if ($response->isOk()) {
            $fm->addSuccessMessage('vehicle-list-printed');
        } else {
            $fm->addErrorMessage('vehicle-list-print-failed');
        }

        return $this->redirect()->toRoute($this->getBaseRoute(), ['action' => null], [], true);
    }

    /**
     * Export vehicles action
     *
     * @return \Laminas\Http\Response
     */
    public function exportAction()
    {
        /**
 * @var \Laminas\Http\Request $request
*/
        $request = $this->getRequest();

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
