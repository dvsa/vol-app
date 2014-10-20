<?php

/**
 * Variation Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractController;
use Olcs\View\Model\Variation\VariationOverview;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Variation Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';

    /**
     * Variation overview
     */
    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        $data = $this->getServiceLocator()->get('Entity\Application')->getOverview($applicationId);

        return new VariationOverview($data, $this->getAccessibleSections());
    }
}
