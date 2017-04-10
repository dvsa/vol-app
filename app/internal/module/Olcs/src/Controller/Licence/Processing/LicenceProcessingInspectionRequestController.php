<?php

/**
 * Licence Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Licence\Processing;

use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\InspectionRequestTrait;
use Dvsa\Olcs\Transfer\Query\Licence\EnforcementArea as LicEnforcementAreaQry;
use Olcs\Data\Mapper\InspectionRequest as InspectionRequestMapper;
use Olcs\Controller\AbstractInternalController;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\LicenceInspectionRequestList as LicenceInspectionRequestListQry;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\InspectionRequest as InspectionRequestQry;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Delete as DeleteDto;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Common\Service\Entity\InspectionRequestEntityService;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Update as UpdateDto;
use Olcs\Form\Model\Form\InspectionRequest;
use Zend\View\Model\ViewModel;

/**
 * Licence Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
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
        'reportType' => InspectionRequestEntityService::REPORT_TYPE_MAINTENANCE_REQUEST,
        'resultType' => InspectionRequestEntityService::RESULT_TYPE_NEW,
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
            $queryToSend = $this->getServiceLocator()
                ->get('TransferAnnotationBuilder')
                ->createQuery(
                    LicEnforcementAreaQry::create(['id' => $this->params()->fromRoute('licence')])
                );

            $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
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
        $service = $this->getServiceLocator()->get('Olcs\Service\Data\OperatingCentresForInspectionRequest');
        $service->setType('licence');
        $service->setIdentifier($this->params()->fromRoute('licence'));
    }

    /**
     * Redirect to index
     *
     * @return Redirect
     */
    public function redirectToIndex()
    {
        $licenceId = $this->params()->fromRoute('licence', null);
        $routeParams = ['licence' => $licenceId];
        return $this->redirect()->toRouteAjax(null, $routeParams);
    }
}
