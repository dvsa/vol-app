<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Traits\EnabledSectionTrait;
use Olcs\View\Model\Application\ApplicationOverview;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController
{
    use ApplicationControllerTrait,
        EnabledSectionTrait;

    protected $lva = 'application';
    protected $location = 'external';

    /**
     * Application overview
     */
    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        $data = $this->getServiceLocator()->get('Entity\Application')->getOverview($applicationId);

        $sections = $this->setEnabledFlagOnSections(
            $this->getAccessibleSections(false),
            $data['applicationCompletions'][0]
        );

        $form = $this->getServiceLocator()
            ->get('Helper\Form')
            ->createForm('Lva\PaymentSubmission')
            ->setData($data);

        $action = $this->url()->fromRoute('application_payment', ['id' => $applicationId]);
        $form->setAttribute('action', $action);

        return new ApplicationOverview($data, $sections, $form);
    }
}
