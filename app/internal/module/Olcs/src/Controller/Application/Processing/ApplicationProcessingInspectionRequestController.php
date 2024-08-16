<?php

namespace Olcs\Controller\Application\Processing;

use Common\RefData;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Application\EnforcementArea as AppEnforcementAreaQry;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\ApplicationInspectionRequestList as ApplicationInspectionRequestListQry;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\InspectionRequest as InspectionRequestQry;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder as TransferAnnotationBuilder;
use Laminas\Http\Response;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\InspectionRequestTrait;
use Olcs\Data\Mapper\InspectionRequest as InspectionRequestMapper;
use Olcs\Form\Model\Form\InspectionRequest;
use Olcs\Service\Data\OperatingCentresForInspectionRequest;

class ApplicationProcessingInspectionRequestController extends AbstractInternalController implements
    LeftViewProvider,
    ApplicationControllerInterface
{
    use InspectionRequestTrait;

    protected $service = 'InspectionRequest';

    protected $type = 'application';

    protected $deleteModalTitle = 'internal.inspection-request.remove-inspection-request';

    protected $enforcementAreaName = '';

    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'inspectionRequest';
    protected $listDto = ApplicationInspectionRequestListQry::class;
    protected $listVars = ['application'];

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
        'reportType'  => RefData::INSPECTION_REPORT_TYPE_MAINTENANCE_REQUEST,
        'resultType'  => RefData::INSPECTION_RESULT_TYPE_NEW,
        'application' => 'route',
        'type'        => 'application'
    ];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = InspectionRequest::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = InspectionRequestMapper::class;

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
    protected FlashMessengerHelperService $flashMessengerHelper;

    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        FlashMessengerHelperService $flashMessenger,
        Navigation $navigation,
        protected TransferAnnotationBuilder $transferAnnotationBuilder,
        protected CachingQueryService $queryService,
        protected OperatingCentresForInspectionRequest $operatingCentresForInspectionRequest
    ) {
        $this->flashMessengerHelper = $flashMessenger;
        parent::__construct($translationHelper, $formHelper, $flashMessenger, $navigation);
    }

    /**
     * get method LeftView
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
        return $this->fromRoute('application');
    }

    /**
     * Get enforcement area name
     *
     * @return string
     */
    protected function getEnforcementAreaName()
    {
        if (!$this->enforcementAreaName) {
            $queryToSend = $this->transferAnnotationBuilder
                ->createQuery(
                    AppEnforcementAreaQry::create(['id' => $this->params()->fromRoute('application')])
                );

            $response = $this->queryService->send($queryToSend);

            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelperService->addErrorMessage('unknown-error');
            }

            if ($response->isOk()) {
                $this->enforcementAreaName = InspectionRequestMapper::mapEnforcementAreaFromApplication(
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

        $service->setType('application');
        $service->setIdentifier($this->params()->fromRoute('application'));
    }

    /**
     * Redirect to index
     *
     * @return Response
     */
    public function redirectToIndex()
    {
        $applicationId = $this->params()->fromRoute('application', null);
        $routeParams = ['application' => $applicationId];
        return $this->redirect()->toRouteAjax(null, $routeParams);
    }
}
