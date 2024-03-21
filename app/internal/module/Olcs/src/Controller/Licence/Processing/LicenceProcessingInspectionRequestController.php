<?php

namespace Olcs\Controller\Licence\Processing;

use Common\RefData;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\InspectionRequest as InspectionRequestQry;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\LicenceInspectionRequestList as LicenceInspectionRequestListQry;
use Dvsa\Olcs\Transfer\Query\Licence\EnforcementArea as LicEnforcementAreaQry;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Traits\InspectionRequestTrait;
use Olcs\Data\Mapper\InspectionRequest as InspectionRequestMapper;
use Olcs\Form\Model\Form\InspectionRequest;
use Olcs\Service\Data\OperatingCentresForInspectionRequest;

class LicenceProcessingInspectionRequestController extends AbstractInternalController implements
    LicenceControllerInterface,
    LeftViewProvider
{
    use InspectionRequestTrait;

    protected $service = 'InspectionRequest';

    protected $type = 'licence';

    protected $deleteModalTitle = 'internal.inspection-request.remove-inspection-request';

    protected $enforcementAreaName = '';

    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'inspectionRequest';
    protected $listDto = LicenceInspectionRequestListQry::class;
    protected $listVars = ['licence'];

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $editViewTemplate = 'sections/processing/pages/form-inspection-request';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = InspectionRequestQry::class;
    protected $itemParams = ['id'];

    protected $inlineScripts = [
        'indexAction' => ['table-actions']
    ];

    protected $deleteCommand = DeleteDto::class;

    protected $defaultData = [
        'reportType' => RefData::INSPECTION_REPORT_TYPE_MAINTENANCE_REQUEST,
        'resultType' => RefData::INSPECTION_RESULT_TYPE_NEW,
        'licence'    => 'route',
        'type'       => 'licence'
    ];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = InspectionRequest::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = InspectionRequestMapper::class;
    protected $addContentTitle = 'Add inspection request';
    protected $editContentTitle = 'Edit inspection request';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * @var string
     */
    protected $section = 'inspection-request';

    protected OperatingCentresForInspectionRequest $operatingCentresForInspectionRequest;
    protected AnnotationBuilder $annotationBuilderService;
    protected QueryService $queryService;
    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessenger,
        Navigation $navigation,
        OperatingCentresForInspectionRequest $operatingCentresForInspectionRequest,
        AnnotationBuilder $annotationBuilderService,
        QueryService $queryService
    ) {
        $this->operatingCentresForInspectionRequest = $operatingCentresForInspectionRequest;
        $this->annotationBuilderService = $annotationBuilderService;
        $this->queryService = $queryService;
        $this->flashMessengerHelper = $flashMessenger;

        parent::__construct($translationHelper, $formHelper, $flashMessenger, $navigation);
    }

    /**
     * get method Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }

    /**
     * Get current licence
     *
     * @return int
     */
    protected function getIdentifier()
    {
        return $this->fromRoute('licence');
    }

    /**
     * Get enforcement area name
     *
     * @return string
     */
    protected function getEnforcementAreaName()
    {
        if (!$this->enforcementAreaName) {
            $queryToSend = $this->annotationBuilderService
                ->createQuery(
                    LicEnforcementAreaQry::create(['id' => $this->params()->fromRoute('licence')])
                );

            $response = $this->queryService->send($queryToSend);

            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelperService->addErrorMessage('unknown-error');
            }

            if ($response->isOk()) {
                $this->enforcementAreaName = InspectionRequestMapper::mapEnforcementAreaFromLicence(
                    $response->getResult()
                );
            }
        }
        return $this->enforcementAreaName;
    }

    /**
     * Setup operating centres listbox
     *
     * @return void
     */
    protected function setUpOcListbox()
    {
        $service = $this->operatingCentresForInspectionRequest;
        $service->setType('licence');
        $service->setIdentifier($this->params()->fromRoute('licence'));
    }

    /**
     * Redirect to index
     *
     * @return \Laminas\Http\Response
     */
    public function redirectToIndex()
    {
        $licenceId = $this->params()->fromRoute('licence', null);
        $routeParams = ['licence' => $licenceId];
        return $this->redirect()->toRouteAjax(null, $routeParams);
    }
}
