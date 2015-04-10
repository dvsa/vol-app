<?php

/**
 * Application Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Application\Processing;

use Olcs\Controller\Traits\InspectionRequestTrait;
use Olcs\Controller\Traits\DeleteActionTrait;

/**
 * Application Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationProcessingInspectionRequestController extends AbstractApplicationProcessingController
{
    use InspectionRequestTrait;
    use DeleteActionTrait;

    protected $headerViewTemplate = 'partials/application-header.phtml';

    protected $service = 'InspectionRequest';

    protected $type = 'application';

    protected $deleteModalTitle = 'internal.inspection-request.remove-inspection-request';

    /**
     * @var string
     */
    protected $section = 'inspection-request';

    /**
     * Get current licence
     * 
     * @return int
     */
    protected function getCurrentLicence()
    {
        $applicationId = $this->fromRoute('application');
        return $this->getServiceLocator()->get('Entity\Application')->getLicenceIdForApplication($applicationId);
    }

    /**
     * Setup operating centres listbox
     */
    protected function setUpOcListbox()
    {
        $service = $this->getServiceLocator()->get('Olcs\Service\Data\OperatingCentresForInspectionRequest');
        $service->setType('application');
        $service->setIdentifier($this->fromRoute('application'));
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
