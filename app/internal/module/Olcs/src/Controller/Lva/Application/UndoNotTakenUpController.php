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
class UndoNotTakenUpController extends AbstractApplicationDecisionController
{
    use ApplicationControllerTrait;

    protected $lva               = 'application';
    protected $location          = 'internal';
    protected $cancelMessageKey  = 'application-not-undo-ntu';
    protected $successMessageKey = 'application-undo-ntu-successfully';
    protected $titleKey          = 'internal-application-undo-ntu-title';

    protected function getForm()
    {
        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal-application-undo-ntu-confirm');

        return $form;
    }

    protected function processDecision($id, $data)
    {
        $this->getServiceLocator()->get('Processing\Application')
            ->processUndoNotTakenUpApplication($id);
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
