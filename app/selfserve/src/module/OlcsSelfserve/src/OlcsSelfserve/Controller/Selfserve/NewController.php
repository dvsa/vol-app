<?php

/**
 * Create a new application.
 *
 * OLCS-437
 *
 * @package		olcs
 * @subpackage	application
 * @author		J Rowbottom <joel.rowbottom@valtech.co.uk>
 */

namespace Olcs\Controller\Application;

use OlcsCommon\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Olcs\Form;
use Zend\Session\Container;
use DateTime;

class NewController extends AbstractActionController
{

    public $messages = null;

    public function indexAction() 
    {
        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'create new application');
        
        $applicationNewForm = new Form\Application\NewForm();
        $request = $this->getRequest();

        $view = new ViewModel(array('applicationNewForm' => $applicationNewForm,
                                       'messages' => $this->messages));
        $view->setTemplate('olcs/application/new');

        return $view;
        
    }
        
    /**
     * Page 2 of the create application process, this page requests the details
     * associated with the application for the given entity type.
     */
    public function detailsAction() {

        // This method is effectively a switch to another action depending on the
        // application entity type being processed.
        $postParams = $this->getRequest()->getPost()->toArray();

        if (isset($postParams['entityTypes']))
        {
            $entityType = $postParams['entityTypes'];

            $params = $this->getEvent()->getRouteMatch()->getParams();
            
            switch($entityType)
            {
                case 'Registered Company':
                    $params['action'] =  'registeredCompany';
                    break;
                case 'Sole Trader':
                    $params['action'] =  'soleTrader';
                    break;
                case 'Partnership':
                    $params['action'] =  'partnership';
                    break;
                case 'Public Authority':
                    $params['action'] =  'publicAuthority';
                    break;
                case 'Other':
                    $params['action'] =  'other';
                    break;
                default:
                      $this->getResponse()->setStatusCode(404);
                      return;
            }
            
            return $this->forward()->dispatch('Olcs\Controller\Application\New', $params);

        } else {
            // if no valid application details, redirect back to page 1? 404 for now
            return $this->redirect()->toURL('/application/new');
        }
        
    }

    /**
     * Action to process registered company details
     */
    public function registeredCompanyAction()
    {
        $data = $this->params()->fromPost();

        if (is_array($data['dateApplicationReceived'])) {
            $data['dateApplicationReceived'] = $data['dateApplicationReceived']['year'] . '-' .
                $data['dateApplicationReceived']['month'] . '-' .
                $data['dateApplicationReceived']['day'];
        }

        
        $companyForm = new Form\Application\RegisteredCompanyDetailsForm();
        $officeForm = new Form\Application\RegisteredOfficeDetailsForm();
        $ownerForm = new Form\Application\IdListForm('owners');
        $subsidiaryForm = new Form\Application\IdListForm('subsidiaries');

        $detailsForm =  new Form\Application\DetailsForm();
        $detailsForm->add($companyForm->setData($data));
        $detailsForm->add($officeForm->setData($data));
        $detailsForm->add($ownerForm->setData($data));
        $detailsForm->add($subsidiaryForm->setData($data));

        if ($this->getRequest()->isPost() && array_key_exists('operatorId', $data)) {
            $applicationId = $this->createApplicationDetails($data);
            
            if (isset($applicationId)) {
                // redirect to update page
                return $this->redirect()->toURL('/application/'.$applicationId.'/details');
            }
              
        }

        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'create new application');

        $view = new ViewModel(array('detailsForm' => $detailsForm,
                                    'messages' => $this->messages,
                                    'application_header_details' => $data,
                                    'entityType' => $data['entityTypes'],
                                    'licenceType' => $data['licenceTypes'],
                                    'operatorType' => $data['operatorTypes'],
                                    'applicationId' => isset($applicationId) ? $applicationId : null
                                ));

        $view->setTemplate('olcs/application/details');
        return $view;        
        
    }
    
    /**
     * Action to process Sole trader details
     */
    public function soleTraderAction()
    {
        $data = $this->params()->fromPost();

        $soleTraderForm = new Form\Application\SoleTraderForm();

        if (is_array($data['dateApplicationReceived'])) {
            $data['dateApplicationReceived'] = $data['dateApplicationReceived']['year'] . '-' .
                $data['dateApplicationReceived']['month'] . '-' .
                $data['dateApplicationReceived']['day'];
        }

        $personSearchForm = new Form\Application\PersonSearchForm();
        $personSearchForm->setAttribute('action', '/application/search/person?' . http_build_query(array(
            'type' => 'application-sole-trader',// used to set the header on the popup form
            'fieldgroup' => '#personSearchForm',
        )));

        $navigation = $this->getServiceLocator()->get('navigation');
        $page = $navigation->findBy('label', 'create new application');
        
        $applicationNewForm = new Form\Application\NewForm();

        $view = new ViewModel(array('applicationNewForm' => $applicationNewForm,
                                    'messages' => $this->messages,
                                    'soleTraderForm' => $soleTraderForm,
                                    'application_header_details' => $data,
                                    'personSearchForm' => $personSearchForm
                                    ));
        $view->setTemplate('olcs/application/soletrader-details-wrapper');

        return $view;        
        
    }
    
    /**
     * Action to process Sole trader details
     */
    public function partnershipAction()
    {
        echo 'partnership page';exit;
        
    }

    private function createNewEntries(array $new)
    {
        $results = [];

        if (isset($new['owners'])) {
            foreach ($new['owners'] as $newOwner) {
                $results['owners'][] = $this->service('Olcs\Person')->create($newOwner)['personId'];
            }
        }

        return $results;
    }

    protected function createApplicationDetails(array $data)
    {
        if (isset($data['new'])) {
            $newEntries      = $this->createNewEntries($data['new']);
            $data['listIds'] = array_flip($newEntries['owners']);
        }

        $tradeType = empty($data['tradingDropdown']) ?
            (empty($data['tradingDetails']) ? null : $data['tradingDetails']) :
            $data['tradingDropdown'];

        $tradingNames = empty($data['tradingNames']) ? array() : $data['tradingNames'];
        array_unshift($tradingNames, $data['tradingNameId']);

        $licenceTypes = array(
            'restricted' => "Restricted",
            'standard national' => "Standard National",
            'standard international' => "Standard International",
            'special restricted' => "Special Restricted"
        );

        $requestBody = array(
            'receivedAt' => (new DateTime($data['dateApplicationReceived']))->format(DateTime::ISO8601),
            'licence' => array(
                'licenceType' => $licenceTypes[$data['licenceTypes']],
                'goodsOrPsv' => $data['operatorTypes'],
                'trafficArea' => $data['trafficAreaType'],
                'tradeType' => $tradeType,
                'tradingNames' => $tradingNames,
                'operator' => array(
                    'version' => $data['operatorVersion'],
                    'operatorId' => $data['operatorId'],
                    'operatorName' => $data['operatorNameTextHidden'],
                    'entityType' => $data['entityTypes'],
                    'owners' => array_keys(empty($data['listIds']) ? array() : $data['listIds']),
                    'registeredCompanyNumber' => $data['companyNumId'],
                    'registeredAddress' => array(
                        'line1' => $data['addressLine1'],
                        'line2' => $data['addressLine2'],
                        'line3' => $data['addressLine3'],
                        'line4' => $data['addressLine4'],
                        'town' => $data['townCity'],
                        'country' => $data['country'],
                        'postcode' => $data['postcode'],
                    ),
                ),
            ),
        );
//var_dump($requestBody);exit;
        $result = $this->service('Olcs\Application')->create($requestBody);

        return $result ? $result['applicationId'] : false;
    }
}
