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
        $form = $this->generateForm(
                'operating-centre', 'processAddForm'
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
                'operating-centre', 'processEditForm', $data
        );

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('self-serve/finance/operating-center/edit');
        return $view;
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
        $addressData = $validData["address"];

        $address = new \OlcsEntities\Entity\Address;
        $address->setAddressLine1($addressData['addressLine1']);
        $address->setAddressLine2($addressData['addressLine2']);
        $address->setAddressLine3($addressData['addressLine3']);
        $address->setCity($addressData['city']);
        $address->setCountry($addressData['country']);
        $address->setPostcode($addressData['postcode']);

        $applicationId = $this->params()->fromRoute('applicationId');
        return array(
            'numberOfVehicles' => $validData['authorised-vehicles']['no-of-vehicles'],
            'numberOfTrailers' => $validData['authorised-vehicles']['no-of-trailers'],
            'sufficientParking' => $validData['authorised-vehicles']['parking-spaces-confirmation'],
            'permission' => $validData['authorised-vehicles']['permission-confirmation'],
            'adPlaced' => $validData['ad-placed'],
            'address' => $address,
            'application' => $applicationId,
        );
    }
}
