<?php
/**
 * Lookup Service API Controller
 *
 * @package     olcs
 * @subpackage  service-api
 * @author      Pelle Wessman <pelle.wessman@valtech.se>
 */

namespace Olcs\Controller\Api;

use OlcsCommon\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class LookupController extends AbstractRestfulController
{
    public function getList()
    {
        $data = array();

        $paginator = new \Olcs\Controller\Plugin\OlcsPaginator();
        $numPerPage = $this->params()->fromQuery('s') ? $this->params()->fromQuery('s') : $paginator->getNumPerPage();
        $currentPage = $this->params()->fromQuery('page') ? $this->params()->fromQuery('page') : 1;

        $type = $this->params()->fromQuery('type', 'licence');


        $type = $this->params()->fromQuery('type', 'licence');
        $searchTerms = $this->params()->fromQuery('search', array());
        $sortColumn = $this->params()->fromQuery('sortColumn', null);
        $sortReversed = $this->params()->fromQuery('sortReversed', false) == true;
        $limit = $this->params()->fromQuery('limit', null);
        $offset = $this->params()->fromQuery('offset', null);

        $sort = array(
            'column' => $sortColumn,
            'dir' => $sortReversed ? 'up' : 'down',
        );

        $lookupService = $this->getServiceLocator()->get('lookupServiceFactory'); 

        if ($type == 'licence') {
            $cleanSearchTerms = $this->pickValidKeys($searchTerms, array(
                'operatorName',
                'operatorTradingName',
                'licenceNumber',
                'postcode',
                'address',
                'town',
                'operatorId',
                'diskSerialNumber',
                'companyNo',
                'vehicleRegMark',
                'fabsRef',
            ));

            // Override pickValidKeys for the filter panels
            $i=0;
            while(isset($searchTerms['f'.$i])) {
                $cleanSearchTerms['f'.$i]=$searchTerms['f'.$i];
            }

            $data = $lookupService->getLicencesData(
                $searchTerms,
                $sortColumn,
                $sortReversed,
                $limit,
                $offset
            );
        } else if ($type == 'person' || $type == 'person-licence') {
            $searchTerms = $this->pickValidKeys($searchTerms, array(
                'firstName',
                'lastName',
                'dateOfBirth',
            ));
            if ($type == 'person-licence') {
                $data = $lookupService->getPersonsAndLicenceData(
                    $searchTerms,
                    $sortColumn,
                    $sortReversed,
                    $limit,
                    $offset
                );
            } else {
                $data['rows'] = $lookupService->getPersonData($searchTerms, $sortColumn, $sortReversed);
            }
        } else {
            $this->response->setStatusCode(400);
            $data['error'] = 'Specified type is invalid';
        }

        return new JsonModel($data);
    }
}
