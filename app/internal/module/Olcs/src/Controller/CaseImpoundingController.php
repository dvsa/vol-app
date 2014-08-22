<?php

/**
 * Case Impounding Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Olcs\Controller;

use Zend\View\Model\ViewModel;
use Zend\Validator\Date as DateValidator;
use Common\Controller\CrudInterface;

/**
 * Class to manage Impounding
 */
class CaseImpoundingController extends CaseController implements CrudInterface
{

    /**
     * Show a table of impounding data for the given case
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');

        $this->checkForCrudAction('case_impounding', array('case' => $caseId, 'licence' => $licenceId), 'id');

        $results = $this->makeRestCall(
            'Impounding',
            'GET',
            array(
                'case' => $caseId,
                'sort' => 'applicationReceiptDate',
                'order' => 'DESC'
            ),
            $this->getIndexBundle()
        );

        $variables = array(
            'tab' => 'impounding',
            'table' => $this->buildTable('impounding', $results['Results'])
        );

        $caseVariables = $this->getCaseVariables($caseId, $variables);
        $view = $this->getView($caseVariables);
        $view->setTemplate('case/manage');

        return $view;
    }

    /**
     * Add impounding data for a case
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $caseId = $this->fromRoute('case');

        $form = $this->generateFormWithData(
            'impounding',
            'processAddImpounding',
            array(
                'case' => $caseId
            )
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Add impounding',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
                'inlineScript' => $this->loadScripts(['impounding']),
            ]
        );

        $view->setTemplate('impounding/form');

        return $view;
    }

    /**
     * Processes the add impounding form
     *
     * @param array $data
     */
    public function processAddImpounding ($data)
    {
        $formattedData = $this->formatForSave($data);

        $result = $this->processAdd($formattedData, 'Impounding');

        if (isset($result['id'])) {
            return $this->redirectToAction();
        }

        return $this->redirectToAction('add');
    }

    /**
     * Processes the edit impounding form
     *
     * @param array $data
     * @return \Zend\Http\Response
     */
    public function processEditImpounding($data)
    {
        $formattedData = $this->formatForSave($data);

        $result = $this->processEdit($formattedData, 'Impounding');

        if (empty($result)) {
            return $this->redirect()->toRoute('case_impounding', ['action' => null, 'id' => null], [], true);
        }

        return $this->redirectToAction('edit');
    }

    /**
     * Loads the edit impounding page
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $licenceId = $this->fromRoute('licence');
        $caseId = $this->fromRoute('case');
        $impoundingId = $this->fromRoute('id');

        $bundle = $this->getFormBundle();

        $details = $this->makeRestCall(
            'Impounding',
            'GET',
            array(
                'id' => $impoundingId,
                'bundle' => json_encode($bundle)
            )
        );

        if (empty($details)) {
            return $this->notFoundAction();
        }

        $data = $this->formatDataForForm($details);

        $form = $this->generateFormWithData(
            'impounding',
            'processEditImpounding',
            $data
        );

        $view = $this->getView(
            [
                'params' => [
                    'pageTitle' => 'Edit impounding',
                    'pageSubTitle' => ''
                ],
                'form' => $form,
                'inlineScript' => $this->loadScripts(['impounding']),
            ]
        );

        $view->setTemplate('impounding/form');

        return $view;
    }

    /**
     * Generate a form with data
     *
     * @param string $name
     * @param callable $callback
     * @param mixed $data
     * @param boolean $tables
     * @return object
     */
    public function generateFormWithData($name, $callback, $data = null, $tables = false)
    {
        $licenceId = $this->fromRoute('licence');

        $form = parent::generateFormWithData($name, $callback, $data, $tables);

        $licence = $this->getLicenceTrafficAreaData($licenceId);

        $legislationCategory = $this->getLegislationCategory($licence);

        $form->get('application_details')
             ->get('impoundingLegislationTypes')
             ->setCategory($legislationCategory);

        $formVenues = $this->getVenueList($licence);
        $form->get('hearing')
            ->get('piVenue')
            ->setValueOptions($formVenues);

        $form->get('outcome')
             ->get('presidingTc')
             ->setValueOptions($this->getListData('PresidingTc', [], 'name', 'id'));

        return $form;
    }

    /**
     *
     * Formats data for use in a table
     *
     * @param array $data
     * @return array
     */
    private function formatForSave($data)
    {
        $formatted = array_merge(array(), $data['outcome'], $data['application_details']);

        $formatted['piVenue'] = $data['hearing']['piVenue'];
        $formatted['piVenueOther'] = $data['hearing']['piVenueOther'];
        $formatted['hearingDate'] = $this->joinHearingDateAndTime(
            $data['hearing']['hearingDate'],
            $data['hearing']['hearingTime']
        );
        //$formatted['presidingTc'] = $formatted['presidingTc'];
        $formatted['id'] = $data['id'];
        $formatted['case'] = $data['case'];
        $formatted['version'] = $data['version'];

        return $formatted;
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

        //hearing date fieldset
        if (!empty($results['hearingDate'])) {
            $formatted['hearing']['hearingTime'] = date('H:i', strtotime($results['hearingDate']));
            $formatted['hearing']['hearingDate'] = $results['hearingDate'];
        }

        if (!empty($results['piVenue'])) {
            $formatted['hearing']['piVenue'] = $results['piVenue']['id'];
        } elseif ($results['piVenueOther']) {
            $formatted['hearing']['piVenue'] = 0;
            $formatted['hearing']['piVenueOther'] = $results['piVenueOther'];
        }

        //application details fieldset
        $formatted['application_details'] = array(
            'impoundingType' => $results['impoundingType']['id'],
            'applicationReceiptDate' => $results['applicationReceiptDate'],
            //'legislationTypes' => $results['legislationTypes'],
            'vrm' => $results['vrm']
        );

        if (!empty($results['impoundingLegislationTypes'])) {
            foreach ($results['impoundingLegislationTypes'] as $legislationType) {
                $formatted['application_details']['impoundingLegislationTypes'][] =
                    $legislationType['id'];
            }
        }
        //outcome fieldset
        $formatted['outcome'] = array(
            'outcomeSentDate' => $results['outcomeSentDate'],
            'notes' => $results['notes']
        );

        if (isset($results['presidingTc']['id'])) {
            $formatted['outcome']['presidingTc'] = $results['presidingTc']['id'];
        }

        if (isset($results['outcome']['id'])) {
            $formatted['outcome']['outcome'] = $results['outcome']['id'];
        }

        $formatted['id'] = $results['id'];
        $formatted['case'] = $results['case']['id'];
        $formatted['version'] = $results['version'];

        return $formatted;
    }

    /**
     * Redirects to the selected action or if no action to the index
     *
     * @param string $action
     * @return \Zend\Http\Response
     */
    private function redirectToAction($action = null)
    {
        return $this->redirect()->toRoute(
            'case_impounding',
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
     * @return \Zend\Http\Response
     */
    private function redirectToCrud($action, $id = null)
    {
        return $this->redirect()->toRoute(
            'case_impounding',
            array(
                'action' => $action,
                'id' => $id,
            ),
            array(),
            true
        );
    }

    /**
     * Hearing date and time are separate fields on the form but are one field in the database
     *
     * @param string $hearingDate
     * @param string $hearingTime
     *
     * @return string
     */
    private function joinHearingDateAndTime($hearingDate, $hearingTime)
    {
        $combined = '';

        if (!empty($hearingDate) && !empty($hearingTime)) {
            $combined = $hearingDate . ' ' . $hearingTime . ':00';
        }

        return $combined;
    }

    private function getVenueList($licence)
    {
        $matchedVenues = array();

        $bundle = array(
            'properties' => 'ALL',
            'children' => array(
                'trafficArea' => array(
                    'properties' => 'ALL'
                )
            )
        );

        $venues = $this->makeRestCall(
            'PiVenue',
            'GET',
            array(
                'limit' => 'all'
            ),
            $bundle
        );

        foreach ($venues['Results'] as $venue) {
            if ($licence['trafficArea']['areaCode'] == $venue['trafficArea']) {
                $matchedVenues[$venue['id']] = $venue['name'];
            }
        }

        $matchedVenues[0] = 'Other';

        return $matchedVenues;
    }

    /**
     * Method to return the bundle required for impounding
     *
     * @return array
     */
    private function getIndexBundle()
    {
        return array(
            'properties' => array(
                'id',
                'applicationReceiptDate',
                'outcomeSentDate'
            ),
            'children' => array(
                'impoundingType' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'presidingTc' => array(
                    'properties' => array(
                        'name'
                    ),
                ),
                'outcome' => array(
                    'properties' => array(
                        'id'
                    ),
                ),
                'impoundingLegislationTypes' => array(
                    'properties' => array(
                        'id'
                    ),
                ),
            )
        );
    }

    /**
     * Returns a bundle to populate the add/edit forms
     *
     * @return array
     */
    private function getFormBundle()
    {
        return array(
            'properties' => array(
                'id',
                'applicationReceiptDate',
                'outcomeSentDate',
                'hearingDate',
                'piVenueOther',
                'vrm',
                'notes',
                'version'
            ),
            'children' => array(
                'impoundingType' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'piVenue' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'presidingTc' => array(
                    'properties' => array(
                        'id',
                        'name'
                    ),
                ),
                'case' => array(
                    'properties' => array(
                        'id'
                    )
                ),
                'hearingLocation' => array(
                    'properties' => array(
                        'id'
                    ),
                ),
                'outcome' => array(
                    'properties' => array(
                        'id'
                    ),
                ),
                'impoundingLegislationTypes' => array(
                    'properties' => array(
                        'id'
                    ),
                ),
            )
        );
    }

    /**
     * Method to return the bundle required for impounding
     *
     * @return array
     */
    private function getLicenceTrafficBundle()
    {
        return array(
            'properties' => 'ALL',
            'children' => array(
                'trafficArea' => array(
                    'properties' => array(
                        'areaCode',
                        'areaName'
                    )
                )
            )
        );
    }

    /**
     * Method to return the relevant select options depending on the licence type
     * Left in here until dynamic list service is available that supports select groups
     *
     * @return array
     */
    private function getLegislationCategory($licence)
    {
        if ($licence['niFlag']) {
            return 'impound_legislation_goods_ni';
        } else {
            if ($licence['goodsOrPsv'] == 'Goods') {
                return 'impound_legislation_goods_gb';
            } else {
                return 'impound_legislation_psv_gb';
            }
        }
    }

    /**
     * Method to return the licence entity with traffic area
     *
     * @param integer $licenceId
     * @return array
     */
    private function getLicenceTrafficAreaData($licenceId)
    {
        $bundle = $this->getLicenceTrafficBundle();
        $licence = $this->makeRestCall(
            'Licence',
            'GET',
            array('id' => $licenceId, 'bundle' => json_encode($bundle))
        );

        return $licence;
    }
}
