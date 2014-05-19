<?php

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller;

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class CaseConvictionController extends CaseController
{
    public function indexAction()
    {
        $postParams = $this->params()->fromPost();
        $routeParams = $this->params()->fromRoute();

        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $routeParams['licence'])));

        if (isset($postParams['action'])) {
            return $this->redirect()->toRoute($postParams['table'], array('licence' => $routeParams['licence'],
                'case' => $routeParams['case'],
                'id' => isset($postParams['id']) ? $postParams['id'] : '',
                'action' => strtolower($postParams['action'])));
        }

        $view = $this->getView();
        $tabs = $this->getTabInformationArray();
        $action = 'convictions';
        $caseId = $routeParams['case'];

        $case = $this->getCase($caseId);

        $form = $this->generateCommentForm($case);

        $summary = $this->getCaseSummaryArray($case);

        $bundle = $this->getIndexBundle();

        $results = $this->makeRestCall(
            'Conviction',
            'GET',
            array(
                'vosaCase' => $caseId,
                'sort' => 'dateOfConviction',
                'order' => 'DESC',
                'bundle' => json_encode($bundle)
            )
        );

        foreach ($results['Results'] as $key => $row) {
            if ($row['category']['id'] != 168) {
                $results['Results'][$key]['categoryText'] = $row['category']['description'];
            }
        }

        $data = [];
        $data['url'] = $this->url();

        $tableBuilder = $this->getServiceLocator()->get('Table');
        $table = $tableBuilder->buildTable('convictions', $results, $data);

        $view->setVariables(
            [
                'case' => $case,
                'tabs' => $tabs,
                'tab' => $action,
                'summary' => $summary,
                'table' => $table,
                'commentForm' => $form,
            ]
        );

        $view->setTemplate('case/manage');
        return $view;
    }

    /**
     * Creates and returns the comment form.
     *
     * @param array $case
     * @return \Zend\Form
     */
    public function generateCommentForm($case)
    {
        $form = $this->generateForm(
            'conviction-comment',
            'saveCommentForm'
        );
        $form->setData($case);

        return $form;
    }

    /**
     * Saves the comment form.
     *
     * @param array $data
     */
    public function saveCommentForm($data)
    {
        $data = array_intersect_key($data, array_flip(['id', 'convictionData', 'version']));
        $this->processEdit($data, 'VosaCase');

        return $this->redirect()->toRoute('case_convictions', [], [], true);
    }

    private function getIndexBundle()
    {
        return array(
            'children' => array(
                'category' => array(
                    'properties' => array(
                        'id',
                        'description'
                    )
                )
            )
        );
    }
}
