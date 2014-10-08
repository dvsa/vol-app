<?php

/**
 * Variation Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Variation;

use Olcs\View\Model\Variation\VariationOverview;

/**
 * Variation Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractVariationController
{
    /**
     * Variation overview
     */
    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        $data = $this->getEntityService('Application')->getOverview($applicationId);

        return new VariationOverview($data, $this->getAccessibleSections());
    }
}
