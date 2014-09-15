<?php

/**
 * Case Public Inquiry Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use Common\Controller\CrudInterface;

/**
 * Class to manage Public Inquiry
 */
class CasePiController extends CaseController implements CrudInterface
{
    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = [
        'children' => [
            'piStatus' => [
                'properties' => 'ALL',
            ],
            'piTypes' => [
                'properties' => 'ALL',
            ],
            'presidingTc' => [
                'properties' =>
                [
                    'id',
                    'name'
                ]
            ],
            'reasons' => [
                'properties' => 'ALL',
                'children' => [
                    'reason' => [
                        'properties' => 'ALL',
                    ]
                ],
            ],
            'piHearings' => array(
                'properties' => 'ALL',
                'children' => [
                    'presidingTc' => [
                        'properties' => 'ALL',
                    ],
                    'presidedByRole' => [
                        'properties' => 'ALL',
                    ],
                ],
            ),
            'decisionPresidingTc' => array(
                'properties' => 'ALL'
            ),
            'decisions' => array(
                'properties' => 'ALL'
            ),
            'assignedTo' => array(
                'properties' => 'ALL'
            ),
            'case' => array(
                'properties' => ['id']
            ),
            'presidedByRole' => array(
                'properties' => ['id']
            )
        ]
    ];

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'Pi';

    /**
     * Data map
     *
     * @var array
     */
    protected $dataMap = array(
        'main' => array(
            'mapFrom' => array(
                'main'
            )
        )
    );

    /**
     * Gets public inquiry data
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $caseId = $this->fromRoute('case');

        $pi = $this->getPiInfoByCaseId($caseId);

        $table = $this->buildPiHearingsTable($pi);

        $variables = array(
            'tab' => 'pi',
            'pi' => $pi,
            'piTable' => $table,
        );

        $caseVariables = $this->getCaseVariables($caseId, $variables);
        $view = $this->getView($caseVariables);
        $view->setTemplate('case/manage');

        return $view;
    }

    public function buildPiHearingsTable($pi)
    {
        if (!isset($pi['piHearings'])) {
            return null;
        }

        return $this->getTable('hearings', $pi['piHearings']);
    }

    public function getPiInfoByCaseId($caseId)
    {
        $bundle = $this->getDataBundle();

        $pis = $this->makeRestCall('Pi', 'GET', array('case' => $caseId, 'limit' => 1), $bundle);

        return current($pis['Results']);
    }

    public function addEditAction()
    {
        $section = $this->params()->fromRoute('section');
        $this->getServiceLocator()->get('Olcs\Service\Data\Licence')->setId($this->params()->fromRoute('licence'));

        if (!in_array($section, ['sla', 'decision', 'agreed'])) {
            throw new \Exception('Invalid section!');
        }

        return call_user_func(array($this, strtolower($section)));
    }

    /**
     * Generate a form with data
     *
     * @param string $name
     * @param callable $callback
     * @param mixed $data
     * @param boolean $tables
     *
     * @return \Zend\Form\Form
     */
    public function generateFormWithData($name, $callback, $data = null, $tables = false)
    {
        $formData = [];

        $form = $this->generateForm($name, $callback, $tables);

        $id = $this->params()->fromRoute('id');

        if ((null !== $id) && null != ($loadedData = $this->load($id))) {

            $loadedData = $this->processLoad($loadedData);
            $formData += $loadedData;
        }

        if (!$this->getRequest()->isPost() /* && is_array($data) */) {
            $formData += $data;

            $form->setData($formData);
        }

        return $form;
    }

    /**
     * Map the data on load, we need to copy the data into the
     * main fieldset.
     *
     * @param array $data
     * @return array
     */
    protected function processLoad($data)
    {
        $data = $this->structureLoadDataForForm($data);

        $data['main'] = $data;

        return $data;
    }

    /**
     * Here we need to restructure the data to properly work
     * with the forms as they require IDs.
     *
     * @param unknown $data
     * @return unknown
     */
    public function structureLoadDataForForm($data)
    {
        $single = [
            'assignedTo', 'piStatus', 'case', 'presidingTc', 'presidedByRole'
        ];

        foreach ($single as $key) {
            if (isset($data[$key]) && is_array($data[$key]) && isset($data[$key]['id'])) {
                $data[$key] = $data[$key]['id'];
            } elseif (isset($data[$key]) && is_array($data[$key]) && count($data[$key]) == 0) {
                $data[$key] = null;
            }
        }

        unset($single, $key);

        $multiple = [
            'piTypes', 'decisions', 'reasons', 'piHearings'
        ];

        foreach ($multiple as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                for ($i=0; $i<count($data[$key]); $i++) {
                    $data[$key][$i] = $data[$key][$i]['id'];
                }
            }
        }

        unset($multiple, $key);

        return $data;
    }

    /**
     * Add Public Inquiry data for a case
     *
     * @return ViewModel
     */
    public function sla()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'pi-sla',
            'processSave',
            array(
                'main' => array('case' => $caseId)
            )
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'SLA',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
            ]
        );

        $view->setTemplate('/form');

        return $view;
    }

    /**
     * Add Public Inquiry agreed data for a case
     *
     * @return ViewModel
     */
    public function agreed()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'PublicInquiryAgreedAndLegislation',
            'processPi',
            array(
                'main' => array('case' => $caseId, 'agreedDate' => date('Y-m-d'))
            )
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Agreed and Legislation',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
            ]
        );

        $view->setTemplate('/form');

        return $view;
    }

    /**
     * Add Public Inquiry decision data for a case
     *
     * @return ViewModel
     */
    public function hearing()
    {
        $caseId = $this->fromRoute('case');

        $pi = $this->getPiInfoByCaseId($caseId);

        return $this->redirect()->toRoute('case_pi_hearing', ['piId' => $pi['id']], [], true);
    }

    /**
     * Add Public Inquiry decision data for a case
     *
     * @return ViewModel
     */
    public function decision()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'pi-decision',
            'processPi',
            array(
                'main' => array('case' => $caseId)
            )
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Register Decision',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
            ]
        );

        $view->setTemplate('/form');

        return $view;
    }

    /**
     * Processes an SLA form
     */
    public function processPi($data)
    {
        if (!(isset($data['main']['id']) && !empty($data['main']['id']))) {
            $data['main']['piStatus'] = 'pi_s_reg';
        }

        $this->processSave($data);

        return $this->redirect()->toRoute('case_pi', ['action' => 'index'], [], true);
    }

    /**
     * Edit Public Inquiry data for a case
     *
     * @return ViewModel
     */
    public function addAction()
    {
        return $this->addEditAction();
    }

    /**
     * Edit Public Inquiry data for a case
     *
     * @return ViewModel
     */
    public function editAction()
    {
        return $this->addEditAction();
    }
}
