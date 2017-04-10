<?php

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

    /**
     * get from
     *
     * @return \Zend\Form\FormInterface
     */
    protected function getForm()
    {
        $request  = $this->getRequest();
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        /** @var \Zend\Form\FormInterface $form */
        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        // override default label on confirm action button
        $form->get('messages')->get('message')->setValue('internal-application-ntu-confirm');

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
            NotTakenUpApplication::create(
                [
                    'id' => $id,
                ]
            )
        );
    }
}
