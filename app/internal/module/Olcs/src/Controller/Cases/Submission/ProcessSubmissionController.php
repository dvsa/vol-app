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
     * Flag to intercept the save and determine whether to return redirect object
     * @var bool
     */
    private $isSaved = false;

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

        if ($this->isSaved) {
            return $this->redirectToIndex();
        }

        $view = $this->getView(['form' => $form, 'title' => $form->getLabel()]);

        $view->setTemplate('pages/form');
        $view->setTerminal(true);

        return $view;
    }

    public function processAssignSave($data)
    {
        $this->getSubmission();

        $data['fields']['senderUser'] = $this->getLoggedInUser();
        $data['fields']['id'] = $this->submission['id'];
        $data['fields']['version'] = $this->submission['version'];

        $response = $this->getServiceLocator()->get('BusinessServiceManager')
            ->get('Cases\Submission\Submission')
            ->process(
                [
                    'data' => $data['fields'],
                ]
            );

        if ($response->isOk()) {
            $this->addSuccessMessage('Saved successfully');
            $this->isSaved = true;
        } else {
            $this->addErrorMessage('Sorry; there was a problem. Please try again.');
        }

        return $this->redirectToIndex();
    }

    /**
     * Simple redirect to index.
     */
    public function redirectToIndex()
    {
        return $this->redirectToRouteAjax(
            'submission',
            ['action'=>'details'],
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }

    private function getSubmission()
    {
        if (empty($this->submission)) {
            $submissionId = $this->params()->fromRoute('submission');

            $submissionService = $this->getServiceLocator()
                ->get('Olcs\Service\Data\Submission');

            $this->submission = $submissionService->fetchData($submissionId);
        }
        return $this->submission;
    }
}
