<?php

/**
 * AuthorisedVehicles Controller
 *
 *
 * @package		selfserve
 * @subpackage  operating-centre
 * @author		Jakub Igla <jakub.igla@valtech.co.uk>
 */

namespace SelfServe\Controller\Finance;

use Common\Controller\FormActionController;
use Zend\View\Model\ViewModel;

class OperatingCentreController extends FormActionController
{
    protected $messages;
    protected $section = 'finance';
    
    /**
     * Action that is responsible for adding operating centre
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction() 
    {
        $applicationId      = $this->params()->fromRoute('applicationId');

        $form = $this->generateForm(
            $this->getFormConfigName($applicationId), 'processAddForm'
        );
        
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('self-serve/finance/operating-center/add');
        return $view;
    }
    
    /**
     * Action that is responsible for editing operating centre
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $operatingCentreId  = $this->params()->fromRoute('operatingCentreId');
        $applicationId      = $this->params()->fromRoute('applicationId');
        
        $data = array(
        	'id' => $operatingCentreId,
            'application' => $applicationId,
        );
        
        //get operating centre enetity based on applicationId and operatingCentreId
        $result = $this->makeRestCall('ApplicationOperatingCentre', 'GET', $data);
        if (empty($result)){
            return $this->notFoundAction();
        }
        
        //hydrate data
        $data = array(
        	'version' => $result['version'],
            'authorised-vehicles' => array(
        	    'no-of-vehicles' => $result['numberOfVehicles'],
                'no-of-trailers' => $result['numberOfTrailers'],
                'parking-spaces-confirmation' => $result['sufficientParking'],
                'permission-confirmation' => $result['permission'],
            ),
        );
        
        //generate form with data
        $form = $this->generateFormWithData(
                $this->getFormConfigName($applicationId), 'processEditForm', $data
        );
        
        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('self-serve/finance/operating-center/edit');
        return $view;
    }

    /**
     * Returns form config name based on licence type
     *
     * @param $applicationId
     * @return string
     */
    public function getFormConfigName($applicationId)
    {
        $licence = $this->makeRestCall('Application', 'GET', ['id' => $applicationId], ['properties' => [], 'children' => ['licence']])['licence'];
        return $licence['goodsOrPsv'] == 'psv' ? 'operating-centre-psv' : 'operating-centre';
    }
    
    
    /**
     * Persist data to database. After that, redirect to Operating centres page
     * 
     * @param array $validData
     * @return void
     */
    public function processAddForm($validData)
    {
        $data = array(
        	'version' => 1,
            'adPlaced' => 'Y',
        ); 
        $data = array_merge($this->mapData($validData), $data);
        
        //persiste to database by calling rest api
        $result = $this->makeRestCall('ApplicationOperatingCentre', 'POST', $data);
        if (isset($result['id'])) {
            $this->redirect()->toRoute('selfserve/finance', array(), true);
        }
    }
    
    /**
     * Persist data to database. After that, redirect to Operating centres page
     *
     * @param array $validData
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function processEditForm($validData)
    {
        $operatingCentreId  = $this->params()->fromRoute('operatingCentreId');
        $data = array(
            'id' => $operatingCentreId,
            'version' => $validData['version'],
        );
        $data = array_merge($this->mapData($validData), $data);
    
        //persiste to database by calling rest api
        $result = $this->makeRestCall('ApplicationOperatingCentre', 'PUT', $data);
        return $this->redirect()->toRoute('selfserve/finance', array(), true);
    }
    
    /**
     * Map common data
     * @param array $validData
     * @return array
     */
    private function mapData($validData)
    {
        $applicationId = $this->params()->fromRoute('applicationId');
        $data = array(
            'numberOfVehicles' => $validData['authorised-vehicles']['no-of-vehicles'],
            'sufficientParking' => $validData['authorised-vehicles']['parking-spaces-confirmation'],
            'permission' => $validData['authorised-vehicles']['permission-confirmation'],
            'application' => $applicationId,
        );

        //licence type condition
        if (isset($validData['authorised-vehicles']['no-of-trailers'])){
            $data = array_merge($data, array('numberOfTrailers' => $validData['authorised-vehicles']['no-of-trailers']));
        }

        return $data;
    }
}
