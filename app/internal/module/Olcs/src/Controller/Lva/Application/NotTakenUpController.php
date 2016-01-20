<?php

/**
 * Application Not Taken Up Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Dvsa\Olcs\Transfer\Command\Application\NotTakenUpApplication;
use Olcs\Controller\Lva\AbstractApplicationDecisionController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Application Not Taken Up Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class NotTakenUpController extends AbstractApplicationDecisionController
{
    use ApplicationControllerTrait;

    protected $lva               = 'application';
    protected $location          = 'internal';
    protected $cancelMessageKey  = 'application-not-ntu';
    protected $successMessageKey = 'application-ntu-successfully';
    protected $titleKey          = 'internal-application-ntu-title';

    protected function getForm()
    {
        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal-application-ntu-confirm');

        return $form;
    }

    protected function processDecision($id, $data)
    {
        $command = NotTakenUpApplication::create(
            [
                'id' => $id
            ]
        );

        $this->handleCommand($command);
    }

    /**
     * Redirect to Application rather than Licence overview page
     */
    protected function redirectOnSuccess($applicationId)
    {
        return $this->redirect()->toRouteAjax(
            'lva-application/overview',
            ['application' => $applicationId]
        );
    }
}
