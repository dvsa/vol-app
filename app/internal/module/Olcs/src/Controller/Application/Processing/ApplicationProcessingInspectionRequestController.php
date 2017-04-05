<?php

/**
 * Application Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Application\Processing;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\InspectionRequestTrait;
use Dvsa\Olcs\Transfer\Query\Application\EnforcementArea as AppEnforcementAreaQry;
use Olcs\Data\Mapper\InspectionRequest as InspectionRequestMapper;
use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\ApplicationInspectionRequestList as ApplicationInspectionRequestListQry;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\InspectionRequest as InspectionRequestQry;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Delete as DeleteDto;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Common\Service\Entity\InspectionRequestEntityService;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Update as UpdateDto;
use Olcs\Form\Model\Form\InspectionRequest;
use Zend\View\Model\ViewModel;

/**
 * Application Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
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
        'reportType'  => InspectionRequestEntityService::REPORT_TYPE_MAINTENANCE_REQUEST,
        'resultType'  => InspectionRequestEntityService::RESULT_TYPE_NEW,
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

            $queryToSend = $this->getServiceLocator()
                ->get('TransferAnnotationBuilder')
                ->createQuery(
                    AppEnforcementAreaQry::create(['id' => $this->params()->fromRoute('application')])
                );

            $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
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
        $service = $this->getServiceLocator()->get('Olcs\Service\Data\OperatingCentresForInspectionRequest');
        $service->setType('application');
        $service->setIdentifier($this->params()->fromRoute('application'));
    }

    /**
     * Redirect to index
     *
     * @return Redirect
     */
    public function redirectToIndex()
    {
        $applicationId = $this->params()->fromRoute('application', null);
        $routeParams = ['application' => $applicationId];
        return $this->redirect()->toRouteAjax(null, $routeParams);
    }
}
