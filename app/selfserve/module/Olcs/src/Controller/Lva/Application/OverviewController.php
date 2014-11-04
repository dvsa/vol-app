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

        $sections = $this->setEnabledAndCompleteFlagOnSections(
            $this->getAccessibleSections(false),
            $data['applicationCompletions'][0]
        );

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper
            ->createForm('Lva\PaymentSubmission')
            ->setData($data);

        $action = $this->url()->fromRoute('application_payment', ['id' => $applicationId]);
        $form->setAttribute('action', $action);

        if (!$this->isApplicationComplete($sections)) {
            // @NOTE: this will need to take account of the application's status
            // too, but we've no UX decision yet as to whether the button will
            // even be shown or not (doesn't really make sense)
            $formHelper->disableElement($form, 'submitPay');
        }

        return new ApplicationOverview($data, $sections, $form);
    }

    private function isApplicationComplete($sections)
    {
        foreach ($sections as $section) {
            if ($section['enabled'] && !$section['complete']) {
                return false;
            }
        }
        return true;
    }
}
