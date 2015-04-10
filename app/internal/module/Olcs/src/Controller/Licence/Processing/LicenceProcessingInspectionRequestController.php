<?php

/**
 * Licence Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Licence\Processing;

use Olcs\Controller\Traits\InspectionRequestTrait;
use Olcs\Controller\Traits\DeleteActionTrait;

/**
 * Licence Processing Inspection Request Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceProcessingInspectionRequestController extends AbstractLicenceProcessingController
{
    use InspectionRequestTrait;
    use DeleteActionTrait;

    protected $service = 'InspectionRequest';

    protected $type = 'licence';

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
        return $this->fromRoute('licence');
    }

    /**
     * Setup operating centres listbox
     */
    protected function setUpOcListbox()
    {
        $service = $this->getServiceLocator()->get('Olcs\Service\Data\OperatingCentresForInspectionRequest');
        $service->setType('licence');
        $service->setIdentifier($this->fromRoute('licence'));
    }

    /**
     * Redirect to index
     *
     * @return Redirect
     */
    public function redirectToIndex()
    {
        $licenceId = $this->fromRoute('licence');
        $routeParams = ['licence' => $licenceId];
        return $this->redirect()->toRouteAjax(null, $routeParams);
    }
}
