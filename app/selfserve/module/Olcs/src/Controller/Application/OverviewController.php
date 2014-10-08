<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\View\Model\Application\ApplicationOverview;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractApplicationController
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

        $licence = $data['licence'];

        // If we don't have licence type, we need to redirect the user to TypeOfLicence
        if ($licence['niFlag'] === null || $licence['licenceType'] === null || $licence['goodsOrPsv'] === null) {
            return $this->redirect()->toRoute('application/type_of_licence', array('id' => $applicationId));
        }

        return new ApplicationOverview($data, $this->getAccessibleSections());
    }
}
