<?php
namespace Olcs\Controller\VCase;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

/**
 * Case submission, generator and viewer.
 *
 * This is the controller that generates and views a submission of a case.
 *
 * @package		olcs
 * @subpackage	submission
 * @author		Pelle Wessman <pelle.wessman@valtech.se>
 */
class SubmissionController extends AbstractActionController
{
    /**
     * The action that generates the Submission
     *
     * A POST-only method that will generate a submission for a case
     */
    public function generatorAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return $this->getResponse()->setStatusCode(405);
        }

        $caseId = (int)$this->getEvent()->getRouteMatch()->getParam('caseId');
        if (!$caseId) {
            $caseId = (int)$request->getPost('caseId');
        }
        $data = $caseId ? $this->service('Olcs\Case')->get($caseId . '/summary') : false;

        if (!$data) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $data['submissionCreatedAt'] = (new \DateTime())->format(\DateTime::ISO8601);
        $data['submissionType'] = 'goods';

        $submissionId = $this->service('Olcs\Submission')->create(array(
            'caseId' => $data['caseId'],
            'createdAt' => $data['submissionCreatedAt'],
            'type' => $data['submissionType'],
            'text' => $this->assembleSubmissionHtml($data)
        ))['submissionId'];

        $query = http_build_query(array('id' => $submissionId, 'caseId' => $data['caseId'], 'licenceId' => $this->getRequest()->getPost('licenceId')));
        return $this->redirect()->toUrl('/case/submission?' . $query);
    }

    public function viewAction()
    {
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'list of cases');
        $page->setParams(array('licenceId' =>  $this->getRequest()->getQuery('licenceId'), 'page' => 1));
        
        $id = (int)$this->getRequest()->getQuery('id');
        $data = $id ? $this->service('Olcs\Submission')->get($id) : false;
        if (!$data) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
        $data['recommendDecision'] = $this->getRecommendDecisionForm($id, $this->getRequest()->getQuery('caseId'), $this->getRequest()->getQuery('licenceId'));
        
        $view = new ViewModel($data);
        $view->setTemplate('olcs/submission/view');

        return $view;
    }
    
    /*
     * creates new submission decision or recommendation
     */
    public function sendAction() 
    {
        if ($this->getRequest()->isPost()) {
            $submissionId = (int)$this->getRequest()->getPost('submissionId');

            if (empty($submissionId)) {
                return $this->getResponse()->setStatusCode(404);
            }

            $params = array();
            $params['senderUserId'] = $this->getRequest()->getPost('senderUserId');
            $params['recipientUserId'] = $this->getRequest()->getPost('recipientUserId');
            $params['senderRole'] = 'user'; //TODO get role when done
            $params['senderLocation'] = 'Leeds'; //TODO get location when done
            $params['comment'] = $this->getRequest()->getPost('subRecomDecNote');
            $params['urgent'] = $this->getRequest()->getPost('urgent');

            if ($this->getRequest()->getPost('recommendActions')) {
                $params['otherText'] = $this->getRequest()->getPost('other');
                $params['type'] = $this->getRequest()->getPost('recommendActions');
                $this->service('Olcs\Submission')->create($submissionId . '/recommendation', $params);
            } elseif($this->getRequest()->getPost('decisionActions')) {
                $params['type'] = $this->getRequest()->getPost('decisionActions');
                $this->service('Olcs\Submission')->create($submissionId . '/decision', $params);
            } else {
                return $this->getResponse()->setStatusCode(404);
            }
            $query = http_build_query(array('caseId' => $this->getRequest()->getPost('caseId')));
            return $this->redirect()->toUrl('/case/'.$this->getRequest()->getPost('licenceId').'/'.$this->getRequest()->getPost('caseId').'/dashboard');
        } else {
            return $this->getResponse()->setStatusCode(405);
        }
    }

    public function ajaxSectionRetrievalAction()
    {
        $id = (int)$this->getRequest()->getQuery('id');
        $section = $this->getRequest()->getQuery('section');

        $data = $id ? $this->service('Olcs\Submission')->get($id) : false;

        if (!$data) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel(array('error' => true));
        }

        $sections = $data['text'];
        $version = $data['version'];

        if (!isset($sections[$section])) {
            $this->getResponse()->setStatusCode(404);
            return new JsonModel(array('error' => true));
        }

        if ($this->getRequest()->isPost()) {
            $versionParam = $this->getRequest()->getPost('version');
            $sections[$section] = $this->getRequest()->getPost('html');

            $updateSuccess = $this->service('Olcs\Submission')->update($id, array(
                'text' => $sections,
            ), $versionParam);

            if (!$updateSuccess) {
                $this->getResponse()->setStatusCode(404);
                return new JsonModel(array('error' => true));
            } else {
                $version = $updateSuccess['version'];
            }
        }

        return new JsonModel(array(
            'section' => $section,
            'version' => $version,
            'html' => $sections[$section],
        ));
    }

    /**
     * Assembles the HTML that the Submission needs in its generation
     *
     * @param array $data Some of the initial data to be used in the partials
     * @return string The generated HTML
     */
    protected function assembleSubmissionHtml(array $data)
    {
        $escaper = new \Zend\Escaper\Escaper('utf-8');

        $parts = array();
        $parts['details'] = $this->renderViewPartial('olcs/submission/partials/details', $data);
        $parts['person-owner'] = $this->renderViewPartial('olcs/submission/partials/person-owner', $data['owners']);

        $licenceId = (int)$data['licence']['licenceId'];
        $transportManager = $this->service('Olcs\Licence')->get($licenceId . '/transport-managers')['rows'];
        $parts['transport-manager'] = $this->renderViewPartial('olcs/submission/partials/transport-manager', array(
            'list' => $transportManager
        ));

        $parts['operating-centre'] = $this->renderViewPartial('olcs/submission/partials/operating-centre', array(
            'list' => $data['operating-centres']
        ));

        $parts['conditions'] = $this->renderViewPartial('olcs/submission/partials/text', array(
            'header' => 'conditions-and-undertaking-for-licences',
            'class' => 'submission-conditions',
            'text' => '<p>' .nl2br($escaper->escapeHtml(implode("\n",$data['licence-conditions'])))  . '</p>',
        ));

        $convictionComment = $this->service('Olcs\Case')->get(intval($data['caseId']) . '/detail-comment');
        $parts['conviction'] = $this->renderViewPartial('olcs/submission/partials/text', array(
            'header' => 'conviction-history',
            'class' => 'submission-conviction',
            'text' => $convictionComment ? $convictionComment['detailComment'] : '',
        ));
        
        // For the additional steps, fetch the additional data in here
        // and then render a new view with that data and append it to $html

        return $parts;
    }
    
    protected function getRecommendDecisionForm($id, $caseId, $licenceId) 
    {
        // Get users for lookup
        $users = $this->service('Olcs\User')->get()['rows'];

        $selectUsers = array();
        foreach ($users as $user) {
            //TODO: Fix, uses displayName instead
            // $selectUsers[$user['userId']] = $user['person']['firstName'] . ' ' . $user['person']['lastName'];
            $selectUsers[$user['userId']] = $user['displayName'];
        }

        $form = new \Olcs\Form\VCase\RecommendDecisionForm();
        
        $recipientUserElem = $form->get('recipientUserId');
        $recipientUserElem->setValueOptions($form->setSelect($selectUsers, 'Select Person'));
        
        $submissionId = $form->get('licenceId');
        $submissionId->setValue($licenceId);
        $submissionId = $form->get('caseId');
        $submissionId->setValue($caseId);
        $submissionId = $form->get('submissionId');
        $submissionId->setValue($id);
        $senderUserId = $form->get('senderUserId');
        $senderUserId->setValue(1); //TODO add in real user when we have login
        $partial = $this->renderViewPartial('olcs/submission/partials/recommend-decision', array('form' => $form));
        return $partial;
    }

    /**
     * Helper method to generate a standalone view template
     *
     * @param string $template The path to the view template
     * @param array $params The view parameters
     * @return string
     */
    protected function renderViewPartial($template, array $params = array())
    {
        $viewRender = $this->getServiceLocator()->get('ViewRenderer');
        $view = new ViewModel($params);
        $view->setTemplate($template);
        return $viewRender->render($view);
    }
    
}
