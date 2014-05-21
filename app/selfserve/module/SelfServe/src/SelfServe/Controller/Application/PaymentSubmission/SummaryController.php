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
        if ($this->isButtonPressed('goToSummary')) {
            return $this->goToSection('Application/ReviewDeclarations/Summary');
        }

        return $this->renderSection();
    }

    /**
     * Placeholdre save method
     *
     * @param array $data
     * @param string $service
     */
    protected function save($data, $service = null)
    {
    }
}
