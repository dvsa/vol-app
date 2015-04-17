<?php

/**
 * Application Undo Not Taken Up Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractApplicationDecisionController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Application Undo Not Taken Up Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ReviveApplicationController extends AbstractApplicationDecisionController
{
    use ApplicationControllerTrait;

    protected $lva               = 'application';
    protected $location          = 'internal';
    protected $cancelMessageKey  = 'application-not-revive-application';
    protected $successMessageKey = 'application-revive-application-successfully';
    protected $titleKey          = 'internal-application-revive-application-confirm';

    protected function getForm()
    {
        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal-application-revive-application-confirm');

        return $form;
    }

    protected function processDecision($id, $data)
    {
        $this->getServiceLocator()->get('Processing\Application')
            ->processReviveApplication($id);
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
