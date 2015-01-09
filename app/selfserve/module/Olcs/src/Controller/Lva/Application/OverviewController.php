<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Olcs\View\Model\Application\ApplicationOverview;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController
{
    use ApplicationControllerTrait;

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
        $data['idIndex'] = $this->getIdentifierIndex();

        $sections = $this->setEnabledAndCompleteFlagOnSections(
            $this->getAccessibleSections(false),
            $data['applicationCompletions'][0]
        );

        $fees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForApplication($applicationId);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper
            ->createForm('Lva\PaymentSubmission')
            ->setData($data);

        if (count($fees)) {
            $fee = $this->getLatestFee($fees);
            $feeAmount = number_format($fee['amount'], 2);
            $form->get('amount')->setTokens([0 => $feeAmount]);
        } else {
            $formHelper->remove($form, 'amount');
            $form->get('submitPay')->setLabel('submit-application.button');
        }

        $action = $this->url()->fromRoute('lva-application/payment', [$this->getIdentifierIndex() => $applicationId]);
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

    /**
     * Helper function to get the latest fee from an array of outstanding fees
     *
     * @param array $fees
     * @return array
     */
    public function getLatestFee(array $fees)
    {
        $latest = null;
        foreach ($fees as $fee) {
            if (
                $latest === null
                ||
                strtotime($fee['invoicedDate']) > strtotime($latest['invoicedDate'])
                ||
                (
                    // edge case - same invoice date, we take the higher id
                    strtotime($fee['invoicedDate']) == strtotime($latest['invoicedDate'])
                    &&
                    $fee['id'] > $latest['id']
                )
            ) {
                $latest = $fee;
            }
        }
        return $latest;
    }
}
