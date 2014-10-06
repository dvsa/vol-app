<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Application\OverviewViewModel;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractApplicationController
{
    /**
     * Application overview
     */
    public function indexAction()
    {
        $applicationId = $this->params()->fromRoute('id');

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        $data = $this->getEntityService('Application')->getOverview($applicationId);

        // If we don't have licence type, we need to redirect the user to TypeOfLicence
        if ($data['licence']['niFlag'] === null
            || $data['licence']['licenceType'] === null
            || $data['licence']['goodsOrPsv'] === null
        ) {
            return $this->redirect()->toRoute('application/type-of-licence', array('id' => $applicationId));
        }

        return new OverviewViewModel($data);
    }

    /**
     * Create application
     */
    public function createAction()
    {
        $organisation = $this->getCurrentOrganisation();
        $application = $this->getEntityService('Application')->createNew($organisation['id']);

        return $this->redirect()->toRoute('application', array('id' => $application['id']));
    }
}
