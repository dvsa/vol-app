<?php

/**
 * Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\PaymentSubmission;

/**
 * Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryController extends PaymentSubmissionController
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
}
