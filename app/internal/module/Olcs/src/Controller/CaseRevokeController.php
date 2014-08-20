<?php

/**
 * Case Revoke Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use Common\Controller\CrudInterface;

/**
 * Class to manage Revokes
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class CaseRevokeController extends CaseController implements CrudInterface
{
    /**
     * Show a table of stays and appeals for the given case
     *
     * @return object
     */
    public function indexAction()
    {
        $routeParams = $this->getParams(['action', 'licence', 'case', 'id']);

        $caseId = $routeParams['case'];

        $this->setBreadcrumbRevoke();

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
        return $this->getRevokeBy('case', $caseId);
    }

    public function getRevoke($revokeId)
    {
        return $this->getRevokeBy('id', $revokeId);
    }

    /**
     * Abstracted away the rest call
     *
     * @param string $by
     * @param mixed $value
     * @return array
     */
    private function getRevokeBy($by, $value)
    {
        $bundle = array(
            'children' => array(
                'reasons' => array(
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

        return $this->makeRestCall('ProposeToRevoke', 'GET', array($by => $value), $bundle);
    }

    /**
     * Search form action
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $this->setBreadcrumbRevoke();

        $routeParams = $this->getParams(['action', 'licence', 'case', 'id']);

        $data = [];
        $data['main'] = ['case' => $routeParams['case']];

        $revoke = array();

        if ($routeParams['action'] == 'edit') {
            $revoke = $this->formatDataForForm(
                $this->getRevoke($routeParams['id'])
            );
        }

        $data['main'] = array_merge($revoke, $data['main']);

        $form = $this->generateFormWithData('revoke', 'processRevoke', $data);

        $view = $this->getView(
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

    public function setBreadcrumbRevoke()
    {
        $routeParams = $this->getParams(['action', 'licence', 'case', 'id']);

        // Below is for setting route params for the breadcrumb
        $this->setBreadcrumb(
            array(
                'licence_case_list/pagination' => array('licence' => $routeParams['licence']),
                'case_revoke' => array('case' => $routeParams['case'], 'licence' => $routeParams['licence'])
            )
        );
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
    public function generateForm($name, $callback, $tables = false)
    {
        unset($tables);

        $licenceId = $this->fromRoute('licence');

        $bundle = array(
            'children' => array(
                'goodsOrPsv' => array(
                    'properties' => array(
                        'id'
                    )
                )
            )
        );

        $licence = $this->makeRestCall(
            'Licence',
            'GET',
            ['id' => $licenceId],
            $bundle
        );

        $form = $this->getForm($name);

        $form->get('main')->get('reasons')->setValueOptions(
            $this->getPiReasonsNvpArray(
                $licence['goodsOrPsv']['id'],
                $licence['niFlag']
            )
        );

        $form->get('main')->get('presidingTc')->setValueOptions(
            $this->getPresidingTcArray()
        );

        return $this->formPost($form, $callback);
    }

    public function getPiReasonsNvpArray($goodsOrPsv, $niFlag)
    {
        switch (strtolower($goodsOrPsv)) {
            case 'lcat_gv':
                $goodsOrPsv = 'GV';
                break;
            case 'lcat_psv':
                $goodsOrPsv = 'PSV';
                break;
        }

        $reasons = $this->makeRestCall(
            'Reason',
            'GET',
            [
                'isProposeToRevoke' => 1,
                'goodsOrPsv' => $goodsOrPsv,
                'isNi' => (int)$niFlag,
                'limit' => 'all'
            ]
        );

        $piReasons = [];

        foreach ($reasons['Results'] as $result) {
            $piReasons[$result['id']] = mb_substr($result['sectionCode'] . ' - ' . $result['description'], 0, 150);
        }

        return $piReasons;
    }

    public function getPresidingTcArray()
    {
        $tc = [];
        $reasons = $this->makeRestCall('PresidingTc', 'GET', []);
        foreach ($reasons['Results'] as $result) {
            $tc[$result['id']] = $result['name'];
        }

        return $tc;
    }

    public function deleteAction()
    {
        $routeParams = $this->getParams(array('action', 'licence', 'case', 'id'));

        $this->makeRestCall('ProposeToRevoke', 'DELETE', array('id' => $routeParams['id']));

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
        if (array_key_exists('cancel-revoke', $data)) {
            unset($data['cancel-revoke']);
        }

        if (isset($data['main'])) {
            $data = $data + $data['main'];
            unset($data['main']);
        }

        $routeParams = $this->getParams(array('action', 'licence', 'case'));

        if ($data['id'] != '') {
            $result = $this->processEdit($data, 'ProposeToRevoke');
        } else {
            $result = $this->processAdd($data, 'ProposeToRevoke');
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
