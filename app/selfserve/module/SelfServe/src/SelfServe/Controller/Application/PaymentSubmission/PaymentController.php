<?php

/**
 * Payment Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\PaymentSubmission;

/**
 * Payment Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PaymentController extends PaymentSubmissionController
{
    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    protected function alterForm($form)
    {
        $form->get('form-actions')->get('submit')->setLabel('Pay and submit');

        return $form;
    }

    /**
     * Placeholder save method
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
    {
    }
}
