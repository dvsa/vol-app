<?php

/**
 * PaymentSubmission Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\PaymentSubmission;

use SelfServe\Controller\Application\ApplicationController;

/**
 * PaymentSubmission Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PaymentSubmissionController extends ApplicationController
{
    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->goToFirstSubSection();
    }
}
