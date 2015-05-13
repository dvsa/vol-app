<?php

/**
 * ProcessSubmissionController
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Submission;

use Olcs\Controller\Interfaces\CaseControllerInterface;
use Zend\View\Model\ViewModel;
use Olcs\Controller\Traits as ControllerTraits;
use Common\Controller\AbstractActionController;

/**
 * ProcessSubmissionController
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ProcessSubmissionController extends AbstractActionController implements CaseControllerInterface
{
    use ControllerTraits\CaseControllerTrait;

    protected $submissionConfig;

    protected $submission;

    /**
     * Processes the send to Form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function assignAction()
    {
        $this->getSubmission();

        $this->fieldValues = $this->params()->fromPost('fields');

        $form = $this->generateFormWithData('submissionSendTo', 'processAssignSave');

        $view = $this->getView(['form' => $form]);

        $view->setTemplate('partials/form');

        return $view;
    }

    public function processAssignSave()
    {
        return $this->redirect()->toRoute(
            'submission',
            [
                'action' => 'details',
                'submission' => $this->submission['id']
            ],
            [],
            true
        );
    }

    private function getSubmission()
    {
        if (empty($this->submission)) {
            $submissionId = $this->params()->fromQuery('submission');

            $submissionService = $this->getServiceLocator()
                ->get('Olcs\Service\Data\Submission');

            $this->submission = $submissionService->fetchData($submissionId);
        }
        return $this->submission;
    }
}
