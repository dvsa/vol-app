<?php

/**
 * Application Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Application\Processing;

use Olcs\Controller\Traits\InspectionRequestTrait;
use Dvsa\Olcs\Transfer\Query\Application\EnforcementArea as AppEnforcementAreaQry;
use Olcs\Data\Mapper\InspectionRequest as InspectionRequestMapper;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\ApplicationInspectionRequestList as ApplicationInspectionRequestListQry;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\InspectionRequest as InspectionRequestQry;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Delete as DeleteDto;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Common\Service\Entity\InspectionRequestEntityService;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\Update as UpdateDto;
use Olcs\Form\Model\Form\InspectionRequest;

/**
 * Application Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationProcessingInspectionRequestController extends AbstractInternalController implements
    PageLayoutProvider,
    PageInnerLayoutProvider,
    ApplicationControllerInterface
{
    use InspectionRequestTrait;

    protected $headerViewTemplate = 'partials/application-header.phtml';

    protected $service = 'InspectionRequest';

    protected $type = 'application';

    protected $deleteModalTitle = 'internal.inspection-request.remove-inspection-request';

    protected $enforcementAreaName = '';

    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'partials/table';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'inspectionRequest';
    protected $listDto = ApplicationInspectionRequestListQry::class;
    protected $listVars = ['application'];

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $editViewTemplate = 'partials/form-inspection-request';
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

    public function getPageLayout()
    {
        return 'layout/application-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/processing-subsection';
    }

    /**
     * @var string
     */
    protected $section = 'inspection-request';

    protected $addSuccessMessage = 'internal-inspection-request-inspection-request-added';
    protected $editSuccessMessage = 'internal-inspection-request-inspection-request-updated';

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
        $applicationId = $this->fromRoute('application');
        $routeParams = ['application' => $applicationId];
        return $this->redirect()->toRouteAjax(null, $routeParams);
    }
}
