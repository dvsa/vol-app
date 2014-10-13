<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Common\Controller\Traits\Lva\EnabledSectionTrait;
use Olcs\View\Model\Application\ApplicationOverview;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractApplicationController
{
    use EnabledSectionTrait;

    /**
     * Application overview
     */
    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        $data = $this->getEntityService('Application')->getOverview($applicationId);

        $sections = $this->setEnabledFlagOnSections(
            $this->getAccessibleSections(false),
            $data['applicationCompletions'][0]
        );

        return new ApplicationOverview($data, $sections);
    }
}
