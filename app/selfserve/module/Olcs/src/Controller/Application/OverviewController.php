<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\Service\Data\SectionConfig;
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

        // If we don't have licence type, we need to redirect the user to TypeOfLicence
        if ($data['licence']['niFlag'] === null
            || $data['licence']['licenceType'] === null
            || $data['licence']['goodsOrPsv'] === null
        ) {
            return $this->redirect()->toRoute('application/type-of-licence', array('id' => $applicationId));
        }

        $access = array(
            'external',
            'application',
            $data['licence']['goodsOrPsv']['id'],
            $data['licence']['licenceType']['id']
        );

        $sectionConfig = new SectionConfig();
        $inputSections = $sectionConfig->getAll();

        $sections = $this->getHelperService('AccessHelper')->setSections($inputSections)
            ->getAccessibleSections($access);

        return new ApplicationOverview($data, array_keys($sections));
    }
}
