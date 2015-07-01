<?php

/**
 * Abstract External Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Application\Application as ApplicationQry;

/**
 * Abstract External Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractOverviewController extends AbstractController
{
    protected $lva;
    protected $location = 'external';

    public function indexAction()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        $data = $this->getOverviewData($applicationId);
        $data['idIndex'] = $this->getIdentifierIndex();

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('Lva\PaymentSubmission');

        $form->setData($data);

        $sections = $this->getSections($data);

        $enabled = $this->isReadyToSubmit($sections);
        $visible = ($data['status']['id'] == RefData::APPLICATION_STATUS_NOT_SUBMITTED);
        $actionUrl = $this->url()->fromRoute(
            'lva-'.$this->lva.'/payment',
            [$this->getIdentifierIndex() => $applicationId]
        );
        $feeAmount = $data['outstandingFeeTotal'];

        $this->getServiceLocator()->get('Helper\PaymentSubmissionForm')
            ->updatePaymentSubmissonForm($form, $actionUrl, $applicationId, $visible, $enabled, $feeAmount);

        return $this->getOverviewView($data, $sections, $form);

    }

    protected function getOverviewData($applicationId)
    {
        $dto = ApplicationQry::create(['id' => $applicationId]);
        $response = $this->handleQuery($dto);

        return $response->getResult();
    }

    abstract protected function getOverviewView($data, $sections, $form);

    abstract protected function getSections($data);
}
