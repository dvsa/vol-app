<?php

/**
 * Case Revoke Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;

/**
 * Class to manage Revokes
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class CaseRevokeController extends CaseController
{
    /**
     * Show a table of stays and appeals for the given case
     *
     * @return object
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');

        if ((int) $caseId == 0) {
            return $this->notFoundAction();
        }

        $licenceId = $this->fromRoute('licence');
        $this->setBreadcrumb(array('licence_case_list/pagination' => array('licence' => $licenceId)));

        $revokes = $this->getRevokes($caseId);

        $variables = array(
            'tab' => 'revoke',
            'revoke' => isset($revokes['Results'][0]) ? $revokes['Results'][0] : null
        );

        $caseVariables = $this->getCaseVariables($caseId, $variables);
        $view = $this->getView($caseVariables);

        $view->setTemplate('case/manage');
        return $view;
    }

    public function getRevokes($caseId)
    {
        $bundle = array(
            'children' => array(
                'piReasons' => array(
                    'properties' => 'ALL'
                ),
                'presidingTc' => array(
                    'properties' => 'ALL'
                ),
                'case' => array(
                    'properties' => 'ALL'
                )
            )
        );

        $revokes = $this->makeRestCall('Revoke', 'GET', array('case' => $caseId), $bundle);

        return $revokes;
    }

    public function getRevoke($revokeId)
    {
        $bundle = array(
            'children' => array(
                'piReasons' => array(
                    'properties' => 'ALL'
                ),
                'presidingTc' => array(
                    'properties' => 'ALL'
                ),
                'case' => array(
                    'properties' => 'ALL'
                )
            )
        );

        $revoke = $this->makeRestCall('Revoke', 'GET', array('id' => $revokeId), $bundle);

        return $revoke;
    }

    /**
     * Search form action
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $routeParams = $this->getParams(array('case', 'licence', 'id', 'action'));

        if (isset($_POST['cancel-revoke'])) {
            return $this->redirect()->toRoute(
                'case_revoke',
                array('case' => $routeParams['case'], 'licence' => $routeParams['licence'], 'action' => 'index')
            );
        }

        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_convictions' => array('case' => $routeParams['case'], 'licence' => $routeParams['licence'])
            )
        );

        $data = ['case' => $routeParams['case']];

        $revoke = array();

        if ($routeParams['action'] == 'edit') {
            $revoke = $this->getRevoke($routeParams['id']);
            $revoke = $this->formatDataForForm($revoke);
        }

        $data = $revoke + $data;

        $form = $this->generateForm('revoke', 'processRevoke');
        $form->setData($data);

        $view = new ViewModel(
            array(
            'form' => $form,
            'params' => array(
                'pageTitle' => 'propose-to-revoke',
                'pageSubTitle' => 'propose-to-revoke-text'
            )
            )
        );
        $view->setTemplate('revoke/form');
        return $view;
    }

    public function formatDataForForm($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (array_key_exists('id', $value)) {
                    $data[$key] = $this->arrayToId($value);
                } else {
                    $data[$key] = $this->formatDataForForm($value);
                }
            }
        }

        return $data;
    }

    public function arrayToId(array $array)
    {
        if (array_key_exists('id', $array)) {
            return $array['id'];
        }

        return null;
    }

    /**
     * Search form action
     *
     * @return ViewModel
     */
    public function editAction()
    {
        return $this->addAction();
    }

    /**
     * Generate a form with a callback
     *
     * @param string $name
     * @param callable $callback
     * @param boolean $tables
     * @return object
     */
    protected function generateForm($name, $callback, $tables = false)
    {
        $form = $this->getForm($name);

        $form->get('piReasons')->setValueOptions($this->getPiReasonsNvpArray());
        $form->get('presidingTc')->setValueOptions($this->getPresidingTcArray());

        if ($tables) {
            return $form;
        }

        return $this->formPost($form, $callback);
    }

    public function getPiReasonsNvpArray()
    {
        $reasons = [];
        $piReasons = $this->makeRestCall('PiReason', 'GET', []);
        foreach ($piReasons['Results'] as $result) {
            $reasons[$result['id']] = $result['sectionCode'] . ' - ' . $result['description'];
        }

        return $reasons;
    }

    public function getPresidingTcArray()
    {
        $tc = [];
        $piReasons = $this->makeRestCall('PresidingTc', 'GET', []);
        foreach ($piReasons['Results'] as $result) {
            $tc[$result['id']] = $result['name'];
        }

        return $tc;
    }

    public function deleteAction()
    {
        $routeParams = $this->getParams(array('action', 'licence', 'case', 'id'));

        $result = $this->makeRestCall('Revoke', 'GET', array('id' => $routeParams['id']));

        if (empty($result)) {
            return $this->notFoundAction();
        }

        $this->makeRestCall('Revoke', 'DELETE', array('id' => $routeParams['id']));

        return $this->redirect()->toRoute(
            'case_revoke',
            array(
                'case' => $routeParams['case'],
                'licence' => $routeParams['licence'],
                'action' => 'index',
                'id' => null
            )
        );
    }

    public function processRevoke($data)
    {
        if (array_key_exists('cancel-revoke', $data)) unset($data['cancel-revoke']);

        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        if ($data['id'] != '') {
            $result = $this->processEdit($data, 'Revoke');
        } else {
            $result = $this->processAdd($data, 'Revoke');
        }

        return $this->redirect()->toRoute(
            'case_revoke',
            array(
                'case' => $routeParams['case'],
                'licence' => $routeParams['licence'],
                'action' => 'index',
                'id' => null
            )
        );
    }
}
