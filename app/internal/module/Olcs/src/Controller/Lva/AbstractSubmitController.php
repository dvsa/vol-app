<?php

namespace Olcs\Controller\Lva;

use Dvsa\Olcs\Transfer\Command\Application\SubmitApplication;

/**
 * Abstract Internal Submit Controller
 *
 * @author Alex Peshkov <alex.peshkov@vltech.co.uk>
 */
abstract class AbstractSubmitController extends AbstractApplicationDecisionController
{
    protected $cancelMessageKey  =  'application-not-submitted';
    protected $successMessageKey =  'application-submitted-successfully';
    protected $titleKey          =  'internal-application-submit-title';

    protected function getForm()
    {
        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal-application-submit-confirm');

        return $form;
    }

    protected function processDecision($id, $data)
    {
        return $this->handleCommand(
            SubmitApplication::create(
                [
                    'id' => $id,
                ]
            )
        );
    }
}
