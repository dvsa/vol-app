<?php
/**
 * @package    Selfserve
 * @subpackage PreviousHistory / ConvictionsAndPenalties
 * @author     Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace SelfServe\Controller\PreviousHistory;

use SelfServe\Controller\AbstractApplicationController;
use Zend\View\Model\ViewModel;

/**
 *
 * @package     Selfserve
 * @subpackage  PreviousHistory / ConvictionsAndPenalties
 * @author		Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ConvictionsAndPenaltiesController extends AbstractApplicationController
{

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->setCurrentSection('previous-history');

    }

    /**
     * Main index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $action = $this->checkForCrudAction();

        if ($action !== false) {
            return $action;
        }

        $applicationId = $this->params()->fromRoute('applicationId');

        $this->setPreviousHistorySubSections();

        $bundle = array(
            'properties' => array(
                'version'
            )
        );

        $data = $this->makeRestCall('Application', 'GET', array('id' => $applicationId), $bundle);

        if (empty($data)) {
            return $this->notFoundAction();
        }

        $results = $this->getConvictionsForApplication($applicationId);

        $table = $this->getCriminalConvitionsTable($results, $applicationId);

        $form = $this->generateFormWithData(
            'conviction-list',
            'processConvictionList',
            $data,
            true
        );

        $form->get('form-actions')->get('home')->setValue(
            $this->getUrlFromRoute(
                'selfserve/declarations',
                ['applicationId' => $applicationId]
            )
        );

        $view = $this->getViewModel(
            array(
                'criminalConvitions' => $table,
                'form' => $form
            )
        );

        $view->setTemplate('self-serve/previous-history/convictions-and-penalties/index');

        return $this->renderLayoutWithSubSections($view, 'convictions-and-penalties', 'previous-history');

    }

    /**
     * Action that is responsible for adding conviction
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {

        $this->setPreviousHistorySubSections();

        $applicationId = $this->params()->fromRoute('applicationId');

        if ($this->isButtonPressed('cancel')) {

            return $this->redirect()->toRoute(
                'selfserve/criminal-convictions',
                array('applicationId' => $applicationId)
            );
        }

        $form = $this->generateForm(
            'conviction',
            'processAddForm'
        );

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/previous-history/convictions-and-penalties/add');
        return $this->renderLayoutWithSubSections($view, 'convictions-and-penalties', 'previous-history');
    }

    /**
     * Persist data to database. After that, redirect to Conviction page
     *
     * @param array $validData
     *
     * @return void
     */
    public function processAddForm($validData)
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $data = array(
            'version' => 1,
            'application' => $applicationId
        );

        $data = array_merge($this->mapData($validData), $data);

        $result = $this->makeRestCall('Conviction', 'POST', $data);

        if (isset($result['id'])) {
            if ($this->isButtonPressed('addAnother')) {

                return $this->redirectToRoute(null, array(), array(), true);
            }

            return $this->redirect()->toRoute(
                'selfserve/criminal-convictions',
                array('applicationId' => $applicationId)
            );

        }
    }

    public function processConvictionList($validData)
    {

    }

    /**
     * Maps data between entity and form
     *
     * @param array $validData
     * @return array
     */
    private function mapData($validData)
    {
        $data = array(
            'personTitle'      => $validData['data']['title'],
            'personFirstname'  => $validData['data']['first_name'],
            'personLastname'   => $validData['data']['last_name'],
            'dateOfConviction' => $validData['data']['doc'],
            'convictionNotes'  => $validData['data']['offence_details'],
            'courtFpm'         => $validData['data']['name_of_court'],
            'penalty'          => $validData['data']['penalty']
        );

        return $data;
    }

    public function editAction()
    {

    }

    /**
     * Delete existing conviction
     *
     */
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        $applicationId = $this->params()->fromRoute('applicationId');

        $this->makeRestCall('Conviction', 'DELETE', array('id' => $id));

        return $this->redirect()->toRoute(
            'selfserve/criminal-convictions',
            array('applicationId' => $applicationId)
        );
    }

    /**
     * Get the criminal convitions table
     *
     * @param array $results
     * @return object
     */
    private function getCriminalConvitionsTable($results, $applicationId)
    {
        $settings = array(
            'sort' => 'address',
            'order' => 'ASC',
            'limit' => 10,
            'page' => 1,
            'url' => $this->getPluginManager()->get('url')
        );

        return $this->getServiceLocator()->get('Table')->buildTable(
            'criminalconvictions',
            $results,
            $settings
        );
    }

    /**
     * Get convictions for application
     *
     * @param int $applicationId
     * @return array
     */
    private function getConvictionsForApplication($applicationId)
    {
        $bundle = array(
            'properties' => array(
                'id',
                'personTitle',
                'personFirstname',
                'personLastname',
                'dateOfConviction',
                'convictionNotes',
                'courtFpm',
                'penalty'
            ),
        );

        $data = $this->makeRestCall(
            'conviction',
            'GET',
            array('application' => $applicationId),
            $bundle
        );

        $finalData = array();
        foreach ($data['Results'] as $result) {
            $finalData[] = $result;
            $lastElemntIndex = count($finalData) - 1;
            $finalData[$lastElemntIndex]['name'] = $finalData[$lastElemntIndex]['personTitle'] . ' ' .
                $finalData[$lastElemntIndex]['personFirstname'] . ' ' .
                $finalData[$lastElemntIndex]['personLastname'];
            unset($finalData[$lastElemntIndex]['personTitle']);
            unset($finalData[$lastElemntIndex]['personFirtName']);
            unset($finalData[$lastElemntIndex]['personLastName']);
        }
        $finalData = array('Count' => count($finalData), 'Results' => $finalData);

        return $finalData;
    }

    /**
     * Sets subsections
     *
     */
    public function setPreviousHistorySubSections()
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $this->setSubSections(
            array(
                'financial-history' => array(
                    'label' => 'selfserve-app-subSection-financial-history',
                    'route' => 'selfserve/previous-history',
                    'routeParams' => array(
                        'applicationId' => $applicationId,
                        'step' => 'previous-history'
                    )
                ),
                'licence-history' => array(
                    'label' => 'selfserve-app-subSection-licence-history',
                    'route' => 'selfserve/business-type',
                    'routeParams' => array(
                        'applicationId' => $applicationId,
                    )
                ),
                'convictions-and-penalties' => array(
                    'label' => 'selfserve-app-subSection-convictions-and-penalties',
                    'route' => 'selfserve/selfserve/previous-history/convictions-penalties',
                    'routeParams' => array(
                        'applicationId' => $applicationId,
                    )
                ),
            )
        );
    }
}
