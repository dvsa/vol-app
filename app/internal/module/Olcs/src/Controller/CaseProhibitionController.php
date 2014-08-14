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
    use DeleteActionTrait;

    /**
     * Index action loads the form data
     *
     * @return \Zend\Form\Form
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');
        $licence = $this->fromRoute('licence');

        $this->checkForCrudAction('case_prohibition', array('case' => $caseId, 'licence' => $licence), 'id');

        $table = $this->generateProhibitionTable($caseId);

        $form = $this->generateProhibitionNoteForm($caseId);

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
                'case_id' => $caseId
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

        $bundle = $this->getBundle();

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
        $formatted['fields']['prohibitionType'] = $results['prohibitionType']['id'];
        $formatted['fields']['isTrailer'] = $results['isTrailer'];

        $formatted['id'] = $results['id'];
        $formatted['case_id'] = $results['case']['id'];
        $formatted['version'] = $results['version'];

        return $formatted;
    }

    /**
     * Gets a table of prohibitions for the specified case
     *
     * @param int $caseId
     * @return string
     */
    private function generateProhibitionTable($caseId)
    {
        $results = $this->makeRestCall('Prohibition', 'GET', array('case' => $caseId), $this->getBundle());

        return $this->buildTable('prohibition', $results);
    }

    /**
     * Creates and returns the prohibition form.
     *
     * @param int $caseId
     * @return \Zend\Form\Form
     */
    private function generateProhibitionNoteForm($caseId)
    {
        $bundle = array(
            'properties' => array(
                'id',
                'version',
                'prohibitionNote'
            )
        );

        $prohibitionNote = $this->makeRestCall('Cases', 'GET', array('id' => $caseId), $bundle);

        if (!isset($prohibitionNote['id'])) {
            $prohibitionNote['id'] = $caseId;
        }

        $data = [
            'main' => $prohibitionNote
        ];

        $form = $this->generateForm(
            'prohibition-comment',
            'saveProhibitionNoteForm'
        );

        $form->setData($data);

        return $form;
    }

    /**
     * Saves the prohibition notes form.
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function saveProhibitionNoteForm($data)
    {
        if (!empty($data['main']['id'])) {
            $this->processEdit($data['main'], 'Cases');
        } else {
            $this->processAdd($data['main'], 'Cases');
        }

        return $this->redirectToRoute('case_prohibition', array(), array(), true);
    }

    /**
     * Processes the add prohibition form
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processAddProhibition($data)
    {
        $formatted = $this->formatForSave($data);

        $result = $this->processAdd($formatted, 'Prohibition');

        if (isset($result['id'])) {
            return $this->redirectToIndex();
        }

        return $this->redirectToRoute('case_prohibition', ['action' => 'add'], [], true);
    }

    /**
     * Processes the edit prohibition form
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processEditProhibition ($data)
    {
        $formattedData = $this->formatForSave($data);

        $result = $this->processEdit($formattedData, 'Prohibition');

        if (empty($result)) {
            return $this->redirectToIndex();
        }

        return $this->redirectToRoute('case_prohibition', ['action' => 'edit'], [], true);
    }

    /**
     * @param array $data
     * @return array
     */
    private function formatForSave($data)
    {
        $formatted = $data['fields'];

        $formatted['id'] = $data['id'];
        $formatted['case'] = $data['case_id'];
        $formatted['version'] = $data['version'];

        return $formatted;
    }

    /**
     * Gets a search bundle
     *
     * @return array
     */
    private function getBundle()
    {
        return array(
            'children' => array(
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'prohibitionType' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );
    }

    /**
     * Should return the name of the service to call for deleting the item
     *
     * @return string
     */
    public function getDeleteServiceName()
    {
        return 'Prohibition';
    }
}
