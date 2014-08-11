<?php

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Olcs\Controller\Traits\DeleteActionTrait;

use Common\Controller\CrudInterface;

/**
 * Case Prohibition Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseProhibitionController extends CaseController implements CrudInterface
{

    /**
     * Index action loads the form data
     *
     * @return \Zend\Form\Form
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');
        $action = $this->fromPost('action');

        if ($action) {
            $action = strtolower($action);

            if ($action == 'add') {
                return $this->redirectToCrud($action, null);
            } elseif ($id) {
                return $this->redirectToCrud($action, $id);
            }
        }

        $prohibition = array();

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'prohibitionType' => array(
                    'properties' => array(
                        'handle',
                        'comment'
                    )
                )
            )
        );

        $results = $this->makeRestCall('Prohibition', 'GET', array('case_id' => $caseId, 'bundle' => json_encode($bundle)));

        if ($results['Count']) {
            $results = $this->formatForTable($results);
            $table = $this->buildTable('prohibition', $results);
        } else {
            $prohibition['case'] = $caseId;
            $table = $this->buildTable('prohibition', []);
        }

        $prohibitionNote = $this->makeRestCall('ProhibitionNote', 'GET', array('case' => $caseId));
        $prohibitionNote['case'] = $caseId;

        if ($prohibitionNote['Count']) {
            $prohibitionNote = $prohibitionNote['Results'][0];
        }

        $prohibitionNote['case'] = $caseId;

        $form = $this->generateProhibitionNoteForm($prohibitionNote);

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licence)));

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();

        $case = $this->getCase($caseId);
        $summary = $this->getCaseSummaryArray($case);

        $view->setVariables(
            [
                'case' => $case,
                'tabs' => $tabs,
                'tab' => 'prohibitions',
                'table' => $table,
                'summary' => $summary,
                'commentForm' => $form,
            ]
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Add action
     *
     * @return void|\Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_prohibition' => array('licence' => $licenceId, 'case' => $caseId)
            )
        );

        $form = $this->generateFormWithData(
            'prohibition',
            'processAddProhibition',
            array(
                'case' => $caseId
            )
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add prohibition',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
            ]
        );

        $view->setTemplate('prohibition/form');

        return $view;
    }

    /**
     * Edit action
     *
     * @return void|\Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');
        $prohibitionId = $this->fromRoute('id');

        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $licenceId),
                'case_prohibition' => array('licence' => $licenceId, 'case' => $caseId)
            )
        );

        $bundle = array(
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'prohibitionType' => array(
                    'properties' => array(
                        'handle',
                        'comment'
                    )
                )
            )
        );

        $details = $this->makeRestCall(
            'Prohibition',
            'GET',
            array(
                'id' => $prohibitionId,
                'bundle' => json_encode($bundle)
            )
        );

        if (empty($details)) {
            return $this->notFoundAction();
        }

        $data = $this->formatDataForForm($details);

        $form = $this->generateFormWithData(
            'prohibition',
            'processEditProhibition',
            $data
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Edit prohibition',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
            ]
        );

        $view->setTemplate('prohibition/form');

        return $view;
    }

    /**
     *
     * Formats data for use in a table
     *
     * @param array $results
     * @return array $results
     */
    private function formatForTable($results)
    {
        $config = $this->getServiceLocator()->get('Config');
        $static = $config['static-list-data'];

        if (!empty($results)) {
            foreach ($results as $key => $result) {
                if (isset($result['prohibitionType']['handle'])
                    && isset($static['prohibition_type'][$result['prohibitionType']['handle']])) {
                    $results[$key]['prohibitionType'] = $static['prohibition_type'][$result['prohibitionType']['handle']];
                }
            }
        }

        return $results;
    }

    /**
     * Formats data for use in the form
     *
     * @param array $results
     * @return array
     */
    private function formatDataForForm($results)
    {
        $formatted = array();

        $formatted['fields']['prohibitionDate'] = $results['prohibitionDate'];
        $formatted['fields']['clearedDate'] = $results['clearedDate'];
        $formatted['fields']['vrm'] = $results['vrm'];
        $formatted['fields']['imposedAt'] = $results['imposedAt'];
        $formatted['fields']['prohibitionType'] = $results['prohibitionType']['handle'];
        $formatted['fields']['isTrailer'] = $results['isTrailer'];

        $formatted['id'] = $results['id'];
        $formatted['case_id'] = $results['case']['id'];
        $formatted['version'] = $results['version'];

        return $formatted;
    }

    /**
     * Creates and returns the prohibition form.
     *
     * @param array $prohibition
     * @return \Zend\Form\Form
     */
    private function generateProhibitionNoteForm($prohibition)
    {
        $form = $this->generateForm(
            'prohibition-comment',
            'saveProhibitionNoteForm'
        );
        $form->setData($prohibition);

        return $form;
    }

    /**
     * Saves the prohibition notes form.
     *
     * @param array $data
     * @return Redirect
     */
    public function saveProhibitionNoteForm($data)
    {
        unset($data['cancel']);

        if ($data['submit'] === '') {
            if (!empty($data['id'])) {
                $this->processEdit($data, 'ProhibitionNote');
            } else {
                $this->processAdd($data, 'ProhibitionNote');
            }
        }

        return $this->redirect()->toRoute('case_prohibition', array(), array(), true);
    }

    /**
     * Processes the add prohibition form
     *
     * @param array $data
     * @return redirect
     */
    public function processAddProhibition ($data)
    {
        $formatted = $data['fields'];
        $result = $this->processAdd($formatted, 'Prohibition');

        if (isset($result['id'])) {
            return $this->redirectToAction();
        }

        return $this->redirectToAction('add');
    }

    /**
     * Processes the edit prohibition form
     *
     * @param array $data
     * @return redirect
     */
    public function processEditProhibition ($data)
    {
        $result = $this->processEdit($data, 'Prohibition');

        if (empty($result)) {
            return $this->redirect()->toRoute(
                'case_prohibition',
                array(
                    'action' => null,
                    'id' => null
                ),
                array(),
                true
            );
        }
    }

    /**
     * Redirects to the selected action or if no action to the index
     *
     * @param string $action
     * @return Redirect
     */
    private function redirectToAction($action = null)
    {
        return $this->redirect()->toRoute(
            'case_prohibition',
            array(
                'action' => $action,
            ),
            array(),
            true
        );
    }

    /**
     * Redirects to the add or edit action
     *
     * @param string $action
     * @param int $id
     * @return Redirect
     */
    private function redirectToCrud($action, $id = null)
    {
        return $this->redirect()->toRoute(
            'case_prohibition',
            array(
                'action' => $action,
                'id' => $id,
            ),
            array(),
            true
        );
    }
}
