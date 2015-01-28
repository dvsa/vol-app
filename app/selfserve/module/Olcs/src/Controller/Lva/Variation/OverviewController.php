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
use Common\Service\Entity\ApplicationEntityService;

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
        $data['idIndex'] = $this->getIdentifierIndex();

        $fee = $this->getServiceLocator()->get('Entity\Fee')
            ->getLatestOutstandingFeeForApplication($applicationId);

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('Lva\PaymentSubmission');

        $form->setData($data);

        $enabled = false; // @TODO - always disabled for now as submit functionality is coming in OLCS-6606
        $visible = ($data['status']['id'] == ApplicationEntityService::APPLICATION_STATUS_NOT_SUBMITTED);

        $formHelper->updatePaymentSubmissonForm($form, $fee, $visible, $enabled);

        return new VariationOverview($data, $this->getAccessibleSections(), $form);
    }
}
