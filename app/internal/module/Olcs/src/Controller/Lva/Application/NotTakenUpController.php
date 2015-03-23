<?php

/**
 * Application Not Taken Up Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

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
        $this->getServiceLocator()->get('Processing\Application')->processNotTakenUpApplication($id);
    }
}
