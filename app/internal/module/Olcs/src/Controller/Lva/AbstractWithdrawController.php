<?php

/**
 * Abstract Internal Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva;

use Olcs\Controller\Lva\AbstractApplicationDecisionController;
use Dvsa\Olcs\Transfer\Command\Application\WithdrawApplication;

/**
 * Abstract Internal Withdraw Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractWithdrawController extends AbstractApplicationDecisionController
{
    protected $cancelMessageKey  =  'application-not-withdrawn';
    protected $successMessageKey =  'application-withdrawn-successfully';
    protected $titleKey          =  'internal-application-withdraw-title';

    protected function getForm()
    {
        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('Withdraw', $request);

        // override default label on confirm action button
        $form->get('form-actions')->get('confirm')->setLabel('Confirm');

        return $form;
    }

    protected function processDecision($id, $data)
    {
        $reason = $data['withdraw-details']['reason'];

        $command = WithdrawApplication::create(
            [
                'id' => $id,
                'reason' => $reason
            ]
        );

        $this->handleCommand($command);
    }
}
