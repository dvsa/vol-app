<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Application\ApplicationOverview;

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
        $applicationId = $this->params('id');

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

        $sections = $this->getHelperService('SectionAccessHelper')
            ->getAccessibleSections($data['licence']['goodsOrPsv']['id'], $data['licence']['licenceType']['id']);

        return new ApplicationOverview($data, array_keys($sections));
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
