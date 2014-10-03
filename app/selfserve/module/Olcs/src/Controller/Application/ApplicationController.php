<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Application;

use Olcs\Controller\AbstractExternalController;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractExternalController
{
    /**
     * Application overview
     */
    public function indexAction()
    {
        $application = $this->params()->fromRoute('id');
        $organisation = $this->getCurrentOrganisation();
        die('here');
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
