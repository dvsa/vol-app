<?php

namespace Olcs\Controller\Lva;

use Dvsa\Olcs\Transfer\Command\Application\ReviveApplication;

/**
 * Class AbstractReviveApplicationController
 *
 * Revive an application.
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
abstract class AbstractReviveApplicationController extends AbstractApplicationDecisionController
{
    protected $lva               = 'application';
    protected $location          = 'internal';
    protected $cancelMessageKey  = 'application-not-revive-application';
    protected $successMessageKey = 'application-revive-application-successfully';
    protected $titleKey          = 'internal-application-revive-application-title';

    /**
     * get Form
     *
     * @return \Common\Form\Form
     */
    protected function getForm()
    {
        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal-application-revive-application-confirm');

        return $form;
    }

    /**
     * process Decision
     *
     * @param int   $id   id
     * @param array $data data
     *
     * @return \Common\Service\Cqrs\Response
     */
    protected function processDecision($id, $data)
    {
        return $this->handleCommand(
            ReviveApplication::create(
                [
                    'id' => $id,
                ]
            )
        );
    }
}
